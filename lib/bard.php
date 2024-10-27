<?php 
defined('ABSPATH') or die();
class BardGenContent {
    private $base_url = AIAUTOTOOL_API_GEM;  

    public function generateRandomDocId() {
        $url = $this->base_url . '/generateRandomDocId';
        return $this->sendGetRequest($url);
    }

    public function generateRandomKey() {
        $url = $this->base_url . '/generateRandomKey';
        return $this->sendGetRequest($url);
    }
    public function maskdown($text) {
        $Parsedown = new Parsedown();
        $html = $Parsedown->text($this->removeImageTags($text));
       
        return $html;
    }
    public function removeImageTags($inputString) {
        if(!empty($inputString)){
            $pattern = '/\[Image[^\]]*\]/';
            $replacement = '';
            $outputString = preg_replace($pattern, $replacement, $inputString);
            return $outputString;
        }else{
            return $inputString;
        }
        
    }
    public function bardrewrite($question, $lang = 'Vietnamese',$toneOfVoice='') {
        $url = $this->base_url . '/bardrewrite';
        $data = ['question' => $question, 'lang' => $lang,'toneOfVoice'=>$toneOfVoice];
        return $this->maskdown($this->sendPostRequest($url, $data));
    }
    public function bardcontentfull($question, $lang = 'Vietnamese') {
        $url = $this->base_url . '/bardcontentfull';
        $data = ['question' => $question, 'lang' => $lang];
        return $this->maskdown($this->sendPostRequest($url, $data));
    }
    public function bardcontent($question, $lang = 'Vietnamese') {
        $url = $this->base_url . '/bardcontent';
        $data = ['question' => $question, 'lang' => $lang];
        return $this->maskdown($this->sendPostRequest($url, $data));
    }
    public function bardcontentmore($question, $lang = 'Vietnamese'){
        $url = $this->base_url . '/bardcontentmore';
        $data = ['question' => $question, 'lang' => $lang];
        return $this->maskdown($this->sendPostRequest($url, $data));
    }
    public function gentitle($question, $lang = 'Vietnamese') {
        $url = $this->base_url . '/gentitle';
        $data = ['question' => $question, 'lang' => $lang];
        return $this->sendPostRequest($url, $data);
    }

    public function searchimg($question) {
        $url = $this->base_url . '/searchimg';
        $data = ['question' => $question];
        return $this->sendPostRequest($url, $data);
    }

    private function sendGetRequest($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function sendPostRequest($url, $data) {
        $data['info'] = rendersetting::aiautotool_getdata();
        $data['domain'] = get_option('home');
        //print_r($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);
        // print_r($response);
        return $response->result;
    }
}
