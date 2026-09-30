<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rss_parser
{
    private $error = '';

    public function error()
    {
        return $this->error;
    }

    public function parse($url)
    {
        if ($url === '') return $this->fail('Please enter an RSS feed URL.');
        if (!preg_match('~^https?://~i', $url)) return $this->fail('URL must start with http:// or https://.');
        if (!filter_var($url, FILTER_VALIDATE_URL)) return $this->fail('The URL format is invalid.');
        $body = $this->fetch($url);
        if ($body === FALSE) return FALSE;
        $previous = libxml_use_internal_errors(TRUE);
        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if ($xml === FALSE) return $this->fail('The URL did not return valid XML.');
        $dom = dom_import_simplexml($xml);
        $is_rss = $dom->localName === 'rss' && (string) $xml['version'] === '2.0';
        $is_atom = $dom->localName === 'feed' && $dom->namespaceURI === 'http://www.w3.org/2005/Atom';
        if (!$is_rss && !$is_atom) return $this->fail('Only RSS 2.0 or Atom feeds are supported.');
        $items = array();
        if ($is_rss) {
            foreach ($xml->channel->item as $item) $items[] = $this->rss_item($item);
        } else {
            foreach ($xml->entry as $item) $items[] = $this->atom_item($item);
        }
        if (!$items) return $this->fail('The feed contains no items.');
        return $items;
    }

    private function fail($message)
    {
        $this->error = $message;
        return FALSE;
    }

    private function fetch($url)
    {
        for ($redirects = 0; $redirects < 5; $redirects++) {
            $host = parse_url($url, PHP_URL_HOST);
            $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
            $port = parse_url($url, PHP_URL_PORT) ?: ($scheme === 'https' ? 443 : 80);
            if (!$host || !in_array($scheme, array('http', 'https'), TRUE) || !in_array($port, array(80, 443), TRUE) || !filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                return $this->fail('The feed URL is unreachable (HTTP 0).');
            }
            $records = dns_get_record($host, DNS_A + DNS_AAAA);
            if (!$records) return $this->fail('The feed URL is unreachable (HTTP 0).');
            $ips = array();
            foreach ($records as $record) {
                $ip = isset($record['ip']) ? $record['ip'] : (isset($record['ipv6']) ? $record['ipv6'] : '');
                if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return $this->fail('The feed URL is unreachable (HTTP 0).');
                $ips[] = $ip;
            }
            $ip = $ips[0];
            $resolved = strpos($ip, ':') !== FALSE ? '['.$ip.']' : $ip;
            $headers = array();
            $handle = curl_init($url);
            curl_setopt_array($handle, array(
                CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_FOLLOWLOCATION => FALSE,
                CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 10,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_RESOLVE => array($host.':'.$port.':'.$resolved),
                CURLOPT_USERAGENT => 'RSS Post Manager/1.0',
                CURLOPT_HEADERFUNCTION => function ($curl, $line) use (&$headers) {
                    if (stripos($line, 'Location:') === 0) $headers['location'] = trim(substr($line, 9));
                    return strlen($line);
                },
                CURLOPT_WRITEFUNCTION => function ($curl, $chunk) use (&$body) {
                    $body .= $chunk;
                    return strlen($body) <= 5 * 1024 * 1024 ? strlen($chunk) : 0;
                }
            ));
            $body = '';
            curl_exec($handle);
            $code = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $error = curl_errno($handle);
            curl_close($handle);
            if ($error) return $this->fail('The feed URL is unreachable (HTTP '.$code.').');
            if ($code >= 300 && $code < 400 && isset($headers['location'])) {
                $next = $headers['location'];
                if (strpos($next, '/') === 0 && strpos($next, '//') !== 0) $next = $scheme.'://'.$host.$next;
                if (!app_safe_url($next)) return $this->fail('The feed URL is unreachable (HTTP '.$code.').');
                $url = $next;
                continue;
            }
            if ($code < 200 || $code >= 300) return $this->fail('The feed URL is unreachable (HTTP '.$code.').');
            return $body;
        }
        return $this->fail('The feed URL is unreachable (HTTP 0).');
    }

    private function rss_item($item)
    {
        $content_ns = $item->children('http://purl.org/rss/1.0/modules/content/');
        $html = (string) $content_ns->encoded ?: (string) $item->description;
        $date = (string) $item->pubDate;
        return $this->normalize((string) $item->title, $html, $this->image($item, $html), $date);
    }

    private function atom_item($item)
    {
        $html = (string) $item->content ?: (string) $item->summary;
        $date = (string) $item->published ?: (string) $item->updated;
        return $this->normalize((string) $item->title, $html, $this->image($item, $html), $date);
    }

    private function normalize($title, $html, $image, $date)
    {
        $content = app_clean_text($html);
        $timestamp = strtotime($date);
        return array(
            'title' => mb_substr(app_clean_text($title), 0, 500, 'UTF-8'),
            'content' => $content,
            'image_url' => $image,
            'char_count' => mb_strlen($content, 'UTF-8'),
            'pub_date' => date('Y-m-d H:i:s', $timestamp ?: time())
        );
    }

    private function image($item, $html)
    {
        foreach ($item->enclosure as $enclosure) {
            if (stripos((string) $enclosure['type'], 'image/') === 0 && app_safe_url((string) $enclosure['url'])) return (string) $enclosure['url'];
        }
        $media = $item->children('http://search.yahoo.com/mrss/');
        foreach ($media->content as $content) {
            if ((string) $content['medium'] === 'image' && app_safe_url((string) $content['url'])) return (string) $content['url'];
        }
        foreach ($media->thumbnail as $thumbnail) {
            if (app_safe_url((string) $thumbnail['url'])) return (string) $thumbnail['url'];
        }
        if (preg_match('~<img\b[^>]*\bsrc\s*=\s*["\']([^"\']+)["\']~i', $html, $match)) return app_safe_url(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        return NULL;
    }
}
