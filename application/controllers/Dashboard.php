<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Post_model', 'Platform_model', 'Post_platform_model'));
    }

    private function data()
    {
        $platforms = $this->Platform_model->all();
        $slug = (string) $this->input->get('platform', TRUE);
        $selected = NULL;
        foreach ($platforms as $platform) if ($platform['slug'] === $slug) $selected = $platform;
        $sort = (string) $this->input->get('sort', TRUE);
        if (!in_array($sort, array('priority', 'date', 'characters', 'title'), TRUE)) $sort = 'priority';
        $dir = strtolower((string) $this->input->get('dir', TRUE)) === 'desc' ? 'desc' : 'asc';
        $total = $this->Post_model->total($selected ? (int) $selected['id'] : NULL, !$selected);
        $page = max(1, min((int) $this->input->get('page'), max(1, (int) ceil($total / 10))));
        $posts = $this->Post_model->page(10, ($page - 1) * 10, $sort, $dir, $selected ? (int) $selected['id'] : NULL, !$selected);
        return array('platforms' => $platforms, 'selected' => $selected, 'counts' => $this->Platform_model->counts(),
            'assigned_total' => $this->Post_model->total(NULL, TRUE), 'all_total' => $this->Post_model->total(),
            'posts' => $posts, 'assigned' => $this->Post_platform_model->for_posts(array_column($posts, 'id')),
            'total' => $total, 'page' => $page, 'pages' => max(1, (int) ceil($total / 10)), 'sort' => $sort, 'dir' => $dir);
    }

    public function index()
    {
        $data = $this->data();
        $this->load->view('layouts/header', array('active' => 'dashboard', 'title' => 'Dashboard'));
        $this->load->view('dashboard/index', $data);
        $this->load->view('layouts/footer');
    }

    public function filter()
    {
        $data = $this->data();
        app_json($this, 'success', 'Dashboard loaded.', array('html' => $this->load->view('dashboard/content', $data, TRUE)));
    }
}
