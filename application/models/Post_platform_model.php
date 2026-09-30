<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Post_platform_model extends CI_Model
{
    public function for_posts($post_ids)
    {
        if (!$post_ids) return array();
        $rows = $this->db->select('post_platforms.post_id, social_platforms.*')
            ->from('post_platforms')->join('social_platforms', 'social_platforms.id = post_platforms.platform_id')
            ->where_in('post_platforms.post_id', $post_ids)->get()->result_array();
        $grouped = array();
        foreach ($rows as $row) $grouped[$row['post_id']][] = $row;
        return $grouped;
    }

    public function replace($post_id, $platform_ids)
    {
        $this->db->delete('post_platforms', array('post_id' => $post_id));
        foreach ($platform_ids as $id) {
            $this->db->insert('post_platforms', array('post_id' => $post_id, 'platform_id' => $id));
        }
    }
}
