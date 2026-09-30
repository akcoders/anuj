<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Feed extends CI_Controller
{
    const PREVIEW_LIFETIME = 1800;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('rss_parser');
        $this->load->model('Post_model');
    }

    public function index()
    {
        $this->load->view('layouts/header', array('active' => 'feed', 'title' => 'Import Feed'));
        $this->load->view('feed/import');
        $this->load->view('layouts/footer');
    }

    public function preview()
    {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $url = trim((string) $this->input->post('feed_url', FALSE));
        $sort = strtoupper((string) $this->input->post('sort_mode', TRUE));
        if (!in_array($sort, array('ASC', 'DESC'), TRUE)) $sort = 'DESC';
        // A failed preview must never leave an older feed available to import.
        $this->session->unset_userdata('feed_preview');
        $items = $this->rss_parser->parse($url);
        if ($items === FALSE) return app_json($this, 'error', $this->rss_parser->error(), array(), 422);
        usort($items, function ($a, $b) use ($sort) {
            $order = strcmp($a['pub_date'], $b['pub_date']);
            return $sort === 'ASC' ? $order : -$order;
        });
        $marked = $this->Post_model->mark_feed_duplicates($items);
        $token = bin2hex(random_bytes(24));
        $this->session->set_userdata('feed_preview', array(
            'token' => $token,
            'expires' => time() + self::PREVIEW_LIFETIME,
            'feed_url' => $url,
            'items' => $items,
            'duplicates' => $marked['indexes']
        ));

        $preview = array();
        foreach ($items as $index => $item) {
            $item['index'] = $index;
            $item['duplicate'] = isset($marked['indexes'][$index]);
            $item['duplicate_reason'] = $item['duplicate'] ? $marked['indexes'][$index] : '';
            $preview[] = $item;
        }
        app_json($this, 'success', 'Feed ready to preview.', array(
            'token' => $token,
            'items' => $preview,
            'total' => count($items),
            'duplicates' => count($marked['indexes']),
            'available' => count($items) - count($marked['indexes'])
        ));
    }

    public function import_direct()
    {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        // A new direct import must invalidate any earlier preview selection.
        $this->session->unset_userdata('feed_preview');
        $url = trim((string) $this->input->post('feed_url', FALSE));
        $sort = strtoupper((string) $this->input->post('sort_mode', TRUE));
        if (!in_array($sort, array('ASC', 'DESC'), TRUE)) $sort = 'DESC';

        $items = $this->rss_parser->parse($url);
        if ($items === FALSE) return app_json($this, 'error', $this->rss_parser->error(), array(), 422);
        usort($items, function ($a, $b) use ($sort) {
            $order = strcmp($a['pub_date'], $b['pub_date']);
            return $sort === 'ASC' ? $order : -$order;
        });

        $result = $this->Post_model->insert_feed_unique($items, $url);
        if ($result === FALSE) return app_json($this, 'error', 'Could not save the feed posts. Please try again.', array(), 500);

        return app_json($this, 'success', $result['imported'] ? 'Posts imported successfully.' : 'No new posts to import.', array(
            'imported' => $result['imported'],
            'skipped' => $result['skipped'],
            'total' => count($items)
        ));
    }

    public function import()
    {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $preview = $this->session->userdata('feed_preview');
        $token = (string) $this->input->post('token', FALSE);
        if (!is_array($preview) || !isset($preview['token'], $preview['expires'], $preview['items'], $preview['feed_url']) ||
            $preview['expires'] < time() || !hash_equals($preview['token'], $token)) {
            return app_json($this, 'error', 'Preview expired. Load the feed again before importing.', array(), 422);
        }

        $mode = (string) $this->input->post('mode', TRUE);
        if (!in_array($mode, array('all', 'selected'), TRUE)) {
            return app_json($this, 'error', 'Choose Import selected or Import all.', array(), 422);
        }
        if ($mode === 'all') {
            $indexes = array_keys($preview['items']);
        } else {
            $submitted = $this->input->post('selected', FALSE);
            if (!is_array($submitted)) $submitted = array();
            $indexes = array();
            foreach ($submitted as $value) {
                if (!is_scalar($value) || !ctype_digit((string) $value) || !array_key_exists((int) $value, $preview['items'])) {
                    return app_json($this, 'error', 'The selection is invalid. Preview the feed again.', array(), 422);
                }
                $indexes[(int) $value] = (int) $value;
            }
            $indexes = array_values($indexes);
            if (!$indexes) return app_json($this, 'error', 'Select at least one new post to import.', array(), 422);
        }

        $items = array();
        $skipped_preview = 0;
        foreach ($indexes as $index) {
            if (isset($preview['duplicates'][$index])) {
                $skipped_preview++;
                continue;
            }
            $items[] = $preview['items'][$index];
        }
        $result = $this->Post_model->insert_feed_unique($items, $preview['feed_url']);
        if ($result === FALSE) return app_json($this, 'error', 'Could not save the feed posts. Please try again.', array(), 500);

        // Tokens are single use. Re-preview to import any other entries later.
        $this->session->unset_userdata('feed_preview');
        $skipped = $skipped_preview + $result['skipped'];
        app_json($this, 'success', $result['imported'] ? 'Posts imported successfully.' : 'No new posts to import.', array(
            'imported' => $result['imported'],
            'skipped' => $skipped
        ));
    }
}
