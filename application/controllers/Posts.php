<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Posts extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Post_model', 'Platform_model', 'Post_platform_model'));
        $this->load->library(array('pagination', 'char_counter'));
    }

    private function render_page($page)
    {
        $total = $this->Post_model->total();
        $pages = max(1, (int) ceil($total / 10));
        $page = max(1, min($page, $pages));
        $posts = $this->Post_model->page(10, ($page - 1) * 10);
        $assigned = $this->Post_platform_model->for_posts(array_column($posts, 'id'));
        $config = array('base_url' => site_url('posts'), 'total_rows' => $total, 'per_page' => 10,
            'use_page_numbers' => TRUE, 'page_query_string' => TRUE, 'query_string_segment' => 'page',
            'num_links' => 2, 'attributes' => array('class' => 'page-link'),
            'first_link' => 'First', 'prev_link' => 'Previous', 'next_link' => 'Next', 'last_link' => 'Last',
            'full_tag_open' => '<nav aria-label="Post pages"><ul class="pagination">', 'full_tag_close' => '</ul></nav>',
            'first_tag_open' => '<li class="page-item page-first">', 'first_tag_close' => '</li>',
            'prev_tag_open' => '<li class="page-item page-prev">', 'prev_tag_close' => '</li>',
            'num_tag_open' => '<li class="page-item">', 'num_tag_close' => '</li>',
            'cur_tag_open' => '<li class="page-item active" aria-current="page"><span class="page-link">', 'cur_tag_close' => '</span></li>',
            'next_tag_open' => '<li class="page-item page-next">', 'next_tag_close' => '</li>',
            'last_tag_open' => '<li class="page-item page-last">', 'last_tag_close' => '</li>');
        $this->pagination->initialize($config);
        return array('posts' => $posts, 'assigned' => $assigned, 'platforms' => $this->Platform_model->all(),
            'pagination' => $this->pagination->create_links(), 'total' => $total, 'page' => $page, 'pages' => $pages);
    }

    public function index()
    {
        $data = $this->render_page((int) $this->input->get('page'));
        $this->load->view('layouts/header', array('active' => 'posts', 'title' => 'Posts'));
        $this->load->view('posts/index', $data);
        $this->load->view('layouts/footer');
    }

    public function list_cards()
    {
        $data = $this->render_page((int) $this->input->get('page'));
        app_json($this, 'success', 'Posts loaded.', array('html' => $this->load->view('posts/list', $data, TRUE), 'total' => $data['total']));
    }

    public function update()
    {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $id = (int) $this->input->post('id');
        $post = $this->Post_model->find($id);
        if (!$post) return app_json($this, 'error', 'Post not found.', array(), 404);
        $title = mb_substr(app_clean_text($this->input->post('title', FALSE)), 0, 500, 'UTF-8');
        $content = app_clean_text($this->input->post('content', FALSE));
        if ($title === '' || $content === '') return app_json($this, 'error', 'Title and content are required.', array(), 422);
        $requested = $this->input->post('platforms');
        if (!is_array($requested)) $requested = array();
        $platforms = $this->Platform_model->all();
        $valid = array_column($platforms, 'id');
        $ids = array_values(array_unique(array_map('intval', $requested)));
        if (count(array_diff($ids, $valid))) return app_json($this, 'error', 'Invalid platform selection.', array(), 422);
        $count = $this->char_counter->count($content);
        $this->db->trans_begin();
        $this->db->update('posts', array('title' => $title, 'content' => $content, 'char_count' => $count), array('id' => $id));
        $this->Post_platform_model->replace($id, $ids);
        if ($this->db->trans_status() === FALSE) { $this->db->trans_rollback(); return app_json($this, 'error', 'Could not save the post.', array(), 500); }
        $this->db->trans_commit();
        $post = $this->Post_model->find($id);
        $assigned = $this->Post_platform_model->for_posts(array($id));
        $x = $this->Platform_model->by_slug('x');
        $warnings = array();
        foreach ($platforms as $platform) {
            if (in_array((int) $platform['id'], $ids, TRUE) && $platform['max_chars'] !== NULL && $count > (int) $platform['max_chars']) {
                $warnings[] = $platform['name'];
            }
        }
        app_json($this, 'success', 'Post updated.', array('html' => $this->load->view('posts/_card', array('post' => $post, 'assigned' => $assigned), TRUE),
            'warning' => $x && in_array((int) $x['id'], $ids, TRUE) && $count > 280, 'warnings' => $warnings));
    }

    public function delete()
    {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        if (!$this->Post_model->delete_and_shift((int) $this->input->post('id'))) return app_json($this, 'error', 'Post not found or could not be deleted.', array(), 404);
        app_json($this, 'success', 'Post deleted.', array('total' => $this->Post_model->total()));
    }

    public function reorder()
    {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $ids = $this->input->post('ids');
        $page = max(1, (int) $this->input->post('page'));
        $expected = array_map('intval', array_column($this->Post_model->page(10, ($page - 1) * 10), 'id'));
        if (!is_array($ids) || count($ids) !== count($expected) || array_diff(array_map('intval', $ids), $expected) || !$this->Post_model->reorder($ids)) {
            return app_json($this, 'error', 'Invalid post order.', array(), 422);
        }
        app_json($this, 'success', 'Post order updated.');
    }
}
