<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function app_clean_text($value)
{
    $value = preg_replace('~<(script|style)\b[^>]*>.*?</\1>~is', '', (string) $value);
    $value = strip_tags($value);
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace('/\s+/u', ' ', $value));
}

function app_safe_url($url)
{
    $url = trim((string) $url);
    return filter_var($url, FILTER_VALIDATE_URL) && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), array('http', 'https'), TRUE) ? $url : NULL;
}

function app_json($controller, $status, $message, $data = array(), $code = 200)
{
    $data['csrf_hash'] = $controller->security->get_csrf_hash();
    $controller->output->set_status_header($code)->set_content_type('application/json', 'utf-8')->set_output(json_encode(array(
        'status' => $status, 'message' => $message, 'data' => $data
    ), JSON_UNESCAPED_UNICODE));
}
