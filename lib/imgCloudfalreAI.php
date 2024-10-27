<?php 
defined('ABSPATH') or die();
class ImgCloudflare {
    private $id_account_cl;
    private $apikey;
    private $url;

    public function __construct($id_account_cl, $apikey) {
        $this->id_account_cl = $id_account_cl;
        $this->apikey = $apikey;
        // $this->url = 'https://api.cloudflare.com/client/v4/accounts/' . $this->id_account_cl . '/ai/run/@cf/bytedance/stable-diffusion-xl-lightning';

        // $this->url = 'https://api.cloudflare.com/client/v4/accounts/' . $this->id_account_cl . '/ai/run/@cf/bytedance/stable-diffusion-xl-lightning';

        // $this->url = 'https://api.cloudflare.com/client/v4/accounts/' . $this->id_account_cl . '/ai/run/@cf/lykon/dreamshaper-8-lcm';
        $this->url = 'https://api.cloudflare.com/client/v4/accounts/' . $this->id_account_cl . '/ai/run/';
        
        $this->translate_url = 'https://api.cloudflare.com/client/v4/accounts/' . $this->id_account_cl . '/ai/run/@cf/meta/m2m100-1.2b';
    }

    public function get_img($prompt,$model_ai) {
        // print_r($prompt);
        $data = json_encode(['prompt' => $this->translate_prompt_to_en($prompt)]);
         // print_r($data);
        $ch = curl_init($this->url.$model_ai);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apikey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code !== 200) {
            throw new Exception('API error: ' . $response);
        }

        curl_close($ch);
        $kq = array(
            'prompt'=>$data,
            'response'=>$response
        );
        return $response;
    }
    public function translate_prompt_to_en($text) {
        // print_r($text);
        $data = json_encode([
            'text' => $text,
            'source_lang' => '', // Automatically detect source language
            'target_lang' => 'english' // Translate to English
        ]);
        
        $ch = curl_init($this->translate_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apikey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code !== 200) {
            throw new Exception('API error: ' . $response);
        }

        curl_close($ch);

        $response_data = json_decode($response, true);
        // print_r($response_data);
        return $response_data['result']['translated_text'] ?? $text;
    }

    public function hungingfaceimg($token, $prompt,$modelai) {
        $url = 'https://api-inference.huggingface.co/models/'.$modelai;
        $data = json_encode(['inputs' => $prompt]);

        $headers = [
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/115.0',
            'Accept: */*',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Content-Type: application/json',
            'x-use-cache: false',
            'Origin: https://huggingface.co',
            'Connection: keep-alive',
            'Authorization: Bearer ' . $token,
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Site: same-site',
            'Pragma: no-cache',
            'Cache-Control: no-cache',
            'TE: trailers'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            return null;
        }

        curl_close($ch);

       
        return $response;
    }
}
