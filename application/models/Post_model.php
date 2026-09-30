<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Post_model extends CI_Model
{
    public function total($platform_id = NULL, $assigned_only = FALSE)
    {
        $this->db->from('posts p');
        if ($platform_id !== NULL) $this->db->join('post_platforms pp', 'pp.post_id = p.id')->where('pp.platform_id', $platform_id);
        elseif ($assigned_only) $this->db->where('EXISTS (SELECT 1 FROM post_platforms pp WHERE pp.post_id = p.id)', NULL, FALSE);
        return (int) $this->db->count_all_results();
    }

    public function page($limit, $offset, $sort = 'priority', $dir = 'asc', $platform_id = NULL, $assigned_only = FALSE)
    {
        $columns = array('priority' => 'p.priority', 'date' => 'p.pub_date', 'characters' => 'p.char_count', 'title' => 'p.title');
        $this->db->select('p.*')->from('posts p');
        if ($platform_id !== NULL) $this->db->join('post_platforms pp', 'pp.post_id = p.id')->where('pp.platform_id', $platform_id);
        elseif ($assigned_only) $this->db->where('EXISTS (SELECT 1 FROM post_platforms pp WHERE pp.post_id = p.id)', NULL, FALSE);
        return $this->db->order_by($columns[$sort], $dir)->order_by('p.id', 'asc')->limit($limit, $offset)->get()->result_array();
    }

    public function find($id)
    {
        return $this->db->get_where('posts', array('id' => $id))->row_array();
    }

    public function insert_feed($items, $feed_url)
    {
        return $this->insert_feed_unique($items, $feed_url) !== FALSE;
    }

    /** Match an article by title and publication date, or by title and text. */
    private function feed_identity_keys($item)
    {
        $title = mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $item['title'])), 'UTF-8');
        $keys = array('date:'.hash('sha256', $title."\0".$item['pub_date']));
        if (trim((string) $item['content']) !== '') {
            $keys[] = 'text:'.hash('sha256', $title."\0".$item['content']);
        }
        return $keys;
    }

    public function mark_feed_duplicates($items)
    {
        $existing = array();
        $titles = array_values(array_unique(array_column($items, 'title')));
        foreach (array_chunk($titles, 100) as $chunk) {
            $rows = $this->db->select('title, content, pub_date')->from('posts')
                ->where_in('title', $chunk)->get()->result_array();
            foreach ($rows as $row) {
                foreach ($this->feed_identity_keys($row) as $key) $existing[$key] = TRUE;
            }
        }

        $seen = array();
        $duplicates = array();
        foreach ($items as $index => $item) {
            $keys = $this->feed_identity_keys($item);
            foreach ($keys as $key) {
                if (isset($existing[$key])) {
                    $duplicates[$index] = 'Already in your library';
                    break;
                }
                if (isset($seen[$key])) {
                    $duplicates[$index] = 'Repeated in this feed';
                    break;
                }
            }
            foreach ($keys as $key) $seen[$key] = TRUE;
        }
        return array('indexes' => $duplicates);
    }

    /** Serialize imports so two requests cannot both pass the duplicate check. */
    public function insert_feed_unique($items, $feed_url)
    {
        if (!$items) return array('imported' => 0, 'skipped' => 0);
        $lock = $this->db->query('SELECT GET_LOCK(?, 10) AS acquired', array('rss_post_manager_import'));
        if (!$lock || (int) $lock->row('acquired') !== 1) return FALSE;

        try {
            $this->db->trans_begin();
            $this->db->query('SELECT id FROM posts ORDER BY priority DESC LIMIT 1 FOR UPDATE');
            $marked = $this->mark_feed_duplicates($items);
            $priority = $this->total();
            $imported = 0;
            foreach ($items as $index => $item) {
                if (isset($marked['indexes'][$index])) continue;
                $item['priority'] = ++$priority;
                $item['feed_url'] = $feed_url;
                $this->db->insert('posts', $item);
                $imported++;
            }
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return FALSE;
            }
            if (!$this->db->trans_commit()) return FALSE;
            return array('imported' => $imported, 'skipped' => count($marked['indexes']));
        } finally {
            $this->db->query('SELECT RELEASE_LOCK(?)', array('rss_post_manager_import'));
        }
    }

    public function delete_and_shift($id)
    {
        $this->db->trans_begin();
        $this->db->query('SELECT id FROM posts ORDER BY priority FOR UPDATE');
        $post = $this->find($id);
        if (!$post) { $this->db->trans_rollback(); return FALSE; }
        $this->db->delete('post_platforms', array('post_id' => $id));
        $this->db->delete('posts', array('id' => $id));
        $this->db->set('priority', 'priority - 1', FALSE)->where('priority >', $post['priority'])->update('posts');
        if ($this->db->trans_status() === FALSE) { $this->db->trans_rollback(); return FALSE; }
        $this->db->trans_commit();
        return TRUE;
    }

    public function reorder($ids)
    {
        $this->db->trans_begin();
        $rows = $this->db->query('SELECT id, priority FROM posts ORDER BY priority, id FOR UPDATE')->result_array();
        $all = array_column($rows, 'id');
        $page = array_values(array_map('intval', $ids));
        if (count($page) !== count(array_unique($page)) || count(array_diff($page, $all)) || !$page) {
            $this->db->trans_rollback(); return FALSE;
        }
        $positions = array();
        foreach ($all as $index => $id) if (in_array((int) $id, $page, TRUE)) $positions[] = $index;
        if (count($positions) !== count($page)) { $this->db->trans_rollback(); return FALSE; }
        foreach ($positions as $index => $position) $all[$position] = $page[$index];
        foreach ($all as $index => $id) $this->db->update('posts', array('priority' => $index + 1), array('id' => $id));
        if ($this->db->trans_status() === FALSE) { $this->db->trans_rollback(); return FALSE; }
        return $this->db->trans_commit();
    }
}
