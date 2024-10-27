<?php 
defined('ABSPATH') or die();
class rendersetting {
    public $languageCodes = [
    'vi' => 'Vietnamese',
    'en' => 'English',
    'th' => 'Thai',
    'ja' => 'Japanese',
    'fr' => 'French',
    'pt' => 'Portuguese',
    'af' => 'Afrikaans',
    'sq' => 'Albanian',
    'am' => 'Amharic',
    'ar' => 'Arabic',
    'hy' => 'Armenian',
    'az' => 'Azerbaijani',
    'eu' => 'Basque',
    'be' => 'Belarusian',
    'bn' => 'Bengali',
    'bs' => 'Bosnian',
    'bg' => 'Bulgarian',
    'ca' => 'Catalan',
    'ceb' => 'Cebuano',
    'ny' => 'Chichewa',
    'zh' => 'Chinese',
    'co' => 'Corsican',
    'hr' => 'Croatian',
    'cs' => 'Czech',
    'da' => 'Danish',
    'nl' => 'Dutch',
    'eo' => 'Esperanto',
    'et' => 'Estonian',
    'tl' => 'Filipino',
    'fi' => 'Finnish',
    'fy' => 'Frisian',
    'gl' => 'Galician',
    'ka' => 'Georgian',
    'de' => 'German',
    'el' => 'Greek',
    'gu' => 'Gujarati',
    'ht' => 'Haitian Creole',
    'ha' => 'Hausa',
    'haw' => 'Hawaiian',
    'he' => 'Hebrew',
    'hi' => 'Hindi',
    'hmn' => 'Hmong',
    'hu' => 'Hungarian',
    'is' => 'Icelandic',
    'ig' => 'Igbo',
    'id' => 'Indonesian',
    'ga' => 'Irish',
    'it' => 'Italian',
    'jv' => 'Javanese',
    'kn' => 'Kannada',
    'kk' => 'Kazakh',
    'km' => 'Khmer',
    'ko' => 'Korean',
    'ku' => 'Kurdish',
    'ky' => 'Kyrgyz',
    'lo' => 'Lao',
    'la' => 'Latin',
    'lv' => 'Latvian',
    'lt' => 'Lithuanian',
    'lb' => 'Luxembourgish',
    'mk' => 'Macedonian',
    'mg' => 'Malagasy',
    'ms' => 'Malay',
    'ml' => 'Malayalam',
    'mt' => 'Maltese',
    'mi' => 'Maori',
    'mr' => 'Marathi',
    'mn' => 'Mongolian',
    'my' => 'Myanmar',
    'ne' => 'Nepali',
    'no' => 'Norwegian',
    'ps' => 'Pashto',
    'fa' => 'Persian',
    'pl' => 'Polish',
    'pa' => 'Punjabi',
    'ro' => 'Romanian',
    'ru' => 'Russian',
    'sm' => 'Samoan',
    'gd' => 'Scots Gaelic',
    'sr' => 'Serbian',
    'st' => 'Sesotho',
    'sn' => 'Shona',
    'sd' => 'Sindhi',
    'si' => 'Sinhala',
    'sk' => 'Slovak',
    'sl' => 'Slovenian',
    'so' => 'Somali',
    'es' => 'Spanish',
    'su' => 'Sundanese',
    'sw' => 'Swahili',
    'sv' => 'Swedish',
    'tg' => 'Tajik',
    'ta' => 'Tamil',
    'te' => 'Telugu',
    'tr' => 'Turkish',
    'uk' => 'Ukrainian',
    'ur' => 'Urdu',
    'uz' => 'Uzbek',
    'cy' => 'Welsh',
    'xh' => 'Xhosa',
    'yi' => 'Yiddish',
    'yo' => 'Yoruba',
    'zu' => 'Zulu',
];

    public $plan = '[
  {
    "product": "aiautotoolpremium",
    "schedule_ai_post": "unlimited",
    "ai_post": "unlimited",
    "auto_general_comment": "unlimited"
  },
  {
    "product": "forme",
    "schedule_ai_post": "unlimited",
    "ai_post": "unlimited",
    "auto_general_comment": "unlimited"
  },
  {
    "product": "aiautotoolpro",
    "schedule_ai_post": 1000,
    "ai_post": 1000,
    "auto_general_comment": 1000
  },
  {
    "product": "free",
    "schedule_ai_post": 50,
    "ai_post": 50,
    "auto_general_comment": 10
  }
]
';

    
    public static $is_premium = false;

    public static function is_premium(){
        $fs   = freemius( 15096 ); 
        return $fs;
        // if ( aiautotool_premium()->is_plan('aiautotoolpro', true) ) {
        //     return $fs;
        // } else {
          
        //     return false;
        // }

    }

    public static function aiautotool_getdata() {
            $data = array();
            // print_r(aiautotool_premium()->_get_license());
            $currentDomain = get_site_url();  
            $fs = rendersetting::is_premium();
            $plan_name = $fs->get_plan_name();
            
            if ($plan_name != 'aiautotoolpro' && $plan_name != 'premium') {
                $accountType = $fs->get_plan_title();
                if ($accountType == 'PLAN_TITLE') {
                    $accountType = 'Free';
                }
            } else {
                $accountType = $fs->get_plan_title();
            }
            if(isset(aiautotool_premium()->_get_license()->secret_key)){
                $secret_key = aiautotool_premium()->_get_license()->secret_key;
            }else{
                $secret_key = '';
            }
            
            $data['install_id'] = '';
            $data['license_id'] ='';
            $data['site_private_key'] = '';
             $data['subscription'] = '';

            if (is_object(aiautotool_premium()->_get_license())) {
                
                
                $data['install_id'] = aiautotool_premium()->get_site()->id;
                $data['license_id'] = aiautotool_premium()->_get_license()->id;
                $data['site_private_key'] = aiautotool_premium()->get_site()->secret_key;
                $data['subscription'] = aiautotool_premium()->_get_subscription($data['license_id']);
                
            } 

            $data['domain'] = $currentDomain;
            
            $data['plan'] = $accountType;
            $data['secret_key'] = $secret_key;
            if(isset($_SERVER['SERVER_ADDR'])){
                $data['IP'] = $_SERVER['SERVER_ADDR'];
            }else{
                $data['IP'] = '';
            }
            
            
            $data['pluginvs'] = AIAUTOTOOL_VS;
            $data['email'] = get_option('admin_email');

            $Commentauto_AI_usage = get_option('Commentauto_AI_usage',0);
            $Autocreatetag_AI_usage = get_option('Autocreatetag_AI_usage',0);
            $Schedule_AI_usage = get_option('Schedule_AI_usage',0);
            $AI_post_usage = get_option('AI_post_usage',0);

            $total_usage = $Commentauto_AI_usage + $Autocreatetag_AI_usage + $Schedule_AI_usage + $AI_post_usage;

            $data['usage']  = $total_usage;
            $data['Commentauto_AI_usage']  = $Commentauto_AI_usage;
            $data['Autocreatetag_AI_usage']  = $Autocreatetag_AI_usage;
            $data['Schedule_AI_usage']  = $Schedule_AI_usage;
            $data['AI_post_usage']  = $AI_post_usage;
             if ($data['plan'] == 'Free') {
            
                    $data['quota'] =  AIAUTOTOOL_FREE;
                } else {
                    
                    $data['quota'] =  -1;
                }
            
            $existing_data = get_option('kct_date_expiration');
            $data['expiration'] =  $existing_data;
            if (empty($existing_data)) {

            $current_date = date('Y-m-d');
            
            $expiration = strtotime('+1 month', strtotime($current_date));
            $expiration = date('Y-m-d', $expiration);
            update_option('kct_date_expiration', array( 'start_date' => $current_date,'expiration'=>$expiration));

                $data['expiration']  = get_option('kct_date_expiration');
            } else {
                // send data to serverapi.
            }

            return $data;
        }
    public static function check_quota() {
    $data = self::aiautotool_getdata();

    if ($data['plan'] == 'Free') {
        
        if ($data['usage'] >= AIAUTOTOOL_FREE) {
            
            return false;
        } else {
            if($data['usage']>=5000){
                return false;
            }
            return true;
        }
    } else {
        if(aiautotool_premium()->_get_subscription(aiautotool_premium()->_get_license()->id)->plan_id==25184){
            if($data['usage']>=5000){
                return false;
            }else{
                return true;
            }
        }
        return true;
    }
}
    public static function get_limit(){
        $data = self::aiautotool_getdata();

        if ($data['plan'] == 'Free') {
            
            return AIAUTOTOOL_FREE;
        } else {
            
            return -1;
        }
    }
    public function render_plan(){

    }
    public function render_setting() {
        // Cài đặt cho lớp cơ sở ở đây
    }
    public function render_tab_setting() {
        // Cài đặt cho lớp cơ sở ở đây
    }
    public function render_feature(){
        
    }
    public function aiautotool_fix_question($prompt, $title = '', $lang = '', $blog = '', $cate = '',$content='') {
       
        $replacements = array(
            '%%title%%' => $title,
            '%%lang%%' => $lang,
            '%%blog%%' => $blog,
            '%%cate%%' => $cate,
            '%%content%%' => $content
        );

        $outprompt = str_replace(array_keys($replacements), array_values($replacements), $prompt);

        return $outprompt;
    }
}

?>