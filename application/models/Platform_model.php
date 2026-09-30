<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Platform_model extends CI_Model
{
    public function all()
    {
        return $this->db->order_by('id', 'ASC')->get('social_platforms')->result_array();
    }

    public function by_slug($slug)
    {
        return $this->db->get_where('social_platforms', array('slug' => $slug))->row_array();
    }

    public function counts()
    {
        $rows = $this->db->select('platform_id, COUNT(*) AS total')->group_by('platform_id')->get('post_platforms')->result_array();
        $counts = array();
        foreach ($rows as $row) {
            $counts[$row['platform_id']] = (int) $row['total'];
        }
        return $counts;
    }
}
