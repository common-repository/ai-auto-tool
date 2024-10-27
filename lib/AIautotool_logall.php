<?php 
defined('ABSPATH') or die();
class AIautoTool_log {
    private $aiautotool_logall;
    public function __construct() {
        $this->aiautotool_logall = get_option('aiautotool_logall', array());
    }
    public function set_log($log_type, $data) {
        $time = current_time('mysql');

        if (!isset($this->aiautotool_logall[$log_type])) {
            $this->aiautotool_logall[$log_type] = array();
        }

        $this->aiautotool_logall[$log_type][] = array(
            'time' => $time,
            'data' => $data,
        );
        update_option('aiautotool_logall', $this->aiautotool_logall);
    }
    public function get_log($log_type = '') {
        if (empty($log_type)) {
            return $this->aiautotool_logall;
        }
        if (isset($this->aiautotool_logall[$log_type])) {
            return $this->aiautotool_logall[$log_type];
        }
        return array();
    }
    private function sort_logs_by_time($logs) {
        // Sử dụng array_multisort để sắp xếp mảng theo thời gian giảm dần
        array_multisort(array_column($logs, 'time'), SORT_DESC, $logs);

        return $logs;
    }
    private function display_table($data) {
        if (!is_object($data)) {
            return ''; // Nếu không phải là đối tượng, trả về chuỗi trống
        }
        
        // Bắt đầu table
       // $table_html = '<table class="widefat fixed">';
        //$table_html .= '<thead><tr><th>Key</th><th>Value</th></tr></thead>';
        //$table_html .= '<tbody>';
       $table_html = '';
        // Duyệt qua thuộc tính của đối tượng và thêm vào table
        foreach ($data as $key => $value) {
            if($key =='post_id'){
                 $table_html.= $key.': <a href="'.get_permalink($value).'">'.$value.'</a>, ';
            }else if($key =='post_title'){
                $table_html.= ' '.$value.', ';
            }else{
                $table_html.= $key.': '.$value.', ';
            }
            // $table_html .= '<tr>';
            // $table_html .= '<td>' . esc_html($key) . '</td>';
            // $table_html .= '<td>' . esc_html($value) . '</td>';
            // $table_html .= '</tr>';
        }

        // Kết thúc table
       // $table_html .= '</tbody></table>';

        return $table_html;
    }
    public function show_log($log_type) {
        // Lấy mảng log_type tương ứng
        $logs = $this->get_log($log_type);
        $logs = $this->sort_logs_by_time($logs);
        // Hiển thị bảng log
        echo '<table class="widefat fixed">';
        echo '<thead><tr><th>Time</th><th>Data</th></tr></thead>';
        echo '<tbody>';

        // Hiển thị từng dòng log
        foreach ($logs as $log) {
        	
            echo '<tr>';
            echo '<td>' . esc_html($log['time']) . '</td>';
            echo '<td>' . $this->display_table((Object)$log['data']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }
}