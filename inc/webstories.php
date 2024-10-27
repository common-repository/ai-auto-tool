<?php
defined('ABSPATH') or die();
class AIautotool_Web_Stories extends rendersetting{

	public  $active = false;
    public  $active_option_name = 'Aiautotool_webstories_active';

    public $slugname = '';
    public $setting ;
    public $namesetting = 'aiautotool_config_webstories_settings';
    public $aiautotool_rewriter_flushed = 'aiautotool_rewriter_flushed';
    public  $icon = '<i class="fa-solid fa-link"></i>';
    private $client = null;
    public $notices = [];
    public $limit = 50;
    private $plan_limit_aiautotool ;
    public $name_plan ;
    public $config = array();
    public $notice ;
    public function __construct() {
    	$this->name_plan =  __('Webstories','ai-auto-tool');
    	$this->setting = get_option($this->namesetting,array());
    	if(!isset($this->setting['logo'])){
    		$this->setting['logo'] = 'https://aiautotool.com/96logo.png';
    	}

    	 $this->active = get_option($this->active_option_name, false);
    	
    	
        if ($this->active=='true') {
            $this->init();
        }
        add_action('wp_ajax_update_active_option_canonical_'.$this->active_option_name, array($this, 'update_active_option_callback'));
        add_action('wp_ajax_nopriv_update_active_option_canonical_'.$this->active_option_name, array($this, 'update_active_option_callback'));

    }

    public function update_active_option_callback() {
        check_ajax_referer('aiautotool_nonce', 'security');
        if (isset($_POST['active'])) {
            $active = sanitize_text_field($_POST['active']);
            update_option($this->active_option_name, $active,null, 'no');
            print_r($active);
        }

        wp_die();
    }
    public function init(){
    	// Hook into WordPress to register the custom URL
        add_action('init', array($this, 'add_custom_rewrite_rule'));

        // Hook into WordPress to add custom rewrite rules
        add_filter('query_vars', array($this, 'custom_rewrite_query_vars'));

        // Hook into WordPress to handle the custom template
        add_filter('template_include', array($this, 'custom_rewrite_rule_template'));
        register_setting('aiautotool_webstories_config_group', $this->namesetting);
        // add_action('do_feed_aiwebstories_feed', array($this, 'custom_feed_template'));


    }
    

    public function render_setting() {
       
        ?>
        <div id="tab-webstorie-setting" class="tab-content" style="display:none;">
            <h2><i class="fa-solid fa-link"></i> Website Stories </h2>
            <p class="ft-note">
                	<i class="fa-solid fa-lightbulb"></i>
                	<?php _e('Link Stories','ai-auto-tool'); ?>: <a href="<?php echo get_home_url();?>/aiwebstories/">Stories</a>
                	<br>
                	<?php _e('Link Feed Stories','ai-auto-tool'); ?>: <a href="<?php echo get_home_url();?>/aiwebstories/feed/">Stories Feed Rss</a>
                </p>

             <form method="post" action="options.php">
                <?php settings_fields('aiautotool_webstories_config_group'); ?>
                <p class="ft-note">
                	<i class="fa-solid fa-lightbulb"></i>
                	Select logo publisher 
                </p>

                <p style="display:flex;">
					<input id="ft-add3" class="ft-input-big" name="<?php echo $this->namesetting; ?>[logo]" type="text" value="<?php if(!empty($this->setting['logo'])){echo sanitize_text_field($this->setting['logo']);} ?>" placeholder="<?php _e('Select logo publisher size : 96x96px','ai-auto-tool'); ?>">
					<button class="ft-selec" data-input-id="ft-add3">Upload</button>
					</p>

				<h3><?php _e('Upload mp3 for storie','ai-auto-tool'); ?></h3>
				<p class="ft-note">
                	<i class="fa-solid fa-lightbulb"></i>
                	<?php _e('Select file mp3 - 1','ai-auto-tool'); ?>
                </p>

                <p style="display:flex;">
					<input id="ft-mp31" class="ft-input-big" name="<?php echo $this->namesetting; ?>[mp31]" type="text" value="<?php if(!empty($this->setting['mp31'])){echo sanitize_text_field($this->setting['mp31']);} ?>" placeholder="<?php _e('Select file mp3','ai-auto-tool'); ?>">
					<button class="ft-selec" data-input-id="ft-mp31">Upload</button>
					</p>
				<p class="ft-note">
                	<i class="fa-solid fa-lightbulb"></i>
                	
                	<?php _e('Select file mp3 - 2','ai-auto-tool'); ?>
                </p>

                <p style="display:flex;">
					<input id="ft-mp32" class="ft-input-big" name="<?php echo $this->namesetting; ?>[mp32]" type="text" value="<?php if(!empty($this->setting['mp32'])){echo sanitize_text_field($this->setting['mp32']);} ?>" placeholder="<?php _e('Select file mp3','ai-auto-tool'); ?>">
					<button class="ft-selec" data-input-id="ft-mp32">Upload</button>
					</p>

				<h3><?php _e('Config webstories','ai-auto-tool'); ?></h3>
				<label class="ft-label-right"><?php _e('Add link stories to post', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('If active , a link storie in article post.', 'ai-auto-tool'); ?></p>

					<!-- tool class 1 -->
					<label class="nut-switch">
					<input type="checkbox" name="<?php echo $this->namesetting; ?>[addstorietopost]" value="1" <?php if ( isset( $this->setting['addstorietopost']) && 1 ==  $this->setting['addstorietopost'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
				<h3><?php _e('Google analytics code','ai-auto-tool'); ?></h3>
				<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Google Analytic ID', 'ai-auto-tool'); ?></p>
					<p>
					<input class="ft-input-big" placeholder="<?php _e('G-xxxxxx', 'ai-auto-tool') ?>" name="<?php echo $this->namesetting; ?>[analytic]" type="text" value="<?php if(!empty($this->setting['analytic'])){echo sanitize_text_field($this->setting['analytic']);} ?>"/>
					</p>
                  <?php submit_button(__( 'Save all', 'ai-auto-tool' ),'ft-submit'); ?>

            </form>
        </div>
            <?php
    }

    public function render_tab_setting() {
        // Cài đặt cho lớp auto_ex_link ở đây
        if ($this->active=='true') {
        echo '<button href="#tab-webstorie-setting" class="nav-tab sotab">'.$this->icon.' WebStories setting</button>';
    	}
    }
    
    public function render_feature(){
        $autoToolBox = new AutoToolBox($this->icon." Active Webstorie", __('If active it, webstories has domain.com/aiwebstories/','ai-auto-tool'), "https://aiautotool.com", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

        echo $autoToolBox->generateHTML();
        ?>
        

        <?php
    }

    // Hàm callback để xử lý yêu cầu khi quy tắc tái viết được kích hoạt
		public function custom_rewrite_rule_handler() {
		    // Thực hiện xử lý tùy chỉnh ở đây
		  ?>
		  <html lang="en-US">
<head>    
	<meta charset="utf-8">   
 <meta name="viewport" content="width=device-width, initial-scale=1">    
	<title>Web Stories</title>   
	 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">   
	 <link rel="canonical" href="<?php echo esc_url($current_url);  ?>/aiwebstories/">  
	  <style type="text/css">
	  	.page-numbers.dots {
    display: none;
}

/* Phần thứ hai: Thiết lập hiệu ứng cho nút trang hiện tại */
.page-numbers.current {
    background-color: #007bff; /* Màu nền của trang hiện tại */
    color: #fff; /* Màu chữ của trang hiện tại */
    border-color: #007bff; /* Màu viền của trang hiện tại */
}

/* Phần thứ ba: Thiết lập kiểu cho nút trang khác */
.page-numbers {
    display: inline-block;
    margin: 0 4px;
    padding: 8px 12px;
    color: #007bff; /* Màu chữ của nút trang khác */
    border: 1px solid #007bff; /* Màu viền của nút trang khác */
    border-radius: 4px;
    text-decoration: none;
    transition: background-color 0.3s, color 0.3s, border-color 0.3s;
}

.page-numbers:hover {
    background-color: #f8f9fa; /* Màu nền khi di chuột qua nút trang khác */
    color: #007bff; /* Màu chữ khi di chuột qua nút trang khác */
    border-color: #007bff; /* Màu viền khi di chuột qua nút trang khác */
}

/* Phần cuối cùng: Thiết lập kiểu cho nút "«" và "»" */
.prev,
.next {
    margin: 0 4px;
    padding: 8px 12px;
    color: #007bff; /* Màu chữ của nút "«" và "»" */
    border: 1px solid #007bff; /* Màu viền của nút "«" và "»" */
    border-radius: 4px;
    text-decoration: none;
    transition: background-color 0.3s, color 0.3s, border-color 0.3s;
}

.prev:hover,
.next:hover {
    background-color: #f8f9fa; /* Màu nền khi di chuột qua nút "«" và "»" */
    color: #007bff; /* Màu chữ khi di chuột qua nút "«" và "»" */
    border-color: #007bff; /* Màu viền khi di chuột qua nút "«" và "»" */
}
	  </style>
	   </head>  
	   <body cz-shortcut-listen="true">    
	   	<div class="container">      
	   		<p class="text-center mt-4">        
	   			<?php echo '<a href="' . esc_url(get_home_url()) . '">' . esc_html(bloginfo('title')) . '</a>'; ?>
	   			<h1 class="text-center mb-5 mt-4">
	   				<span class="display-4">Web Stories</span></h1>

	   				<div class="row justify-content-center">
	   					

	   						<?php 
	   						// Retrieve and display the latest 30 posts
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			$args = array(
			    'post_type'      => 'post',
			    'post_status'    => 'publish',
			    'posts_per_page' => 30,
			    'orderby'        => 'date',
			    'order'          => 'DESC',
			    'meta_query'     => array(
			        array(
			            'key'     => '_thumbnail_id',
			            'compare' => 'EXISTS',
			        ),
			    ),
			    'paged'          => $paged,
			);

			$query = new WP_Query($args);

			if ($query->have_posts()) {
			    while ($query->have_posts()) {
			        $query->the_post();

			        $post_title = get_the_title();
			        $post_name = get_post_field('post_name', $post->ID);
			        $post_thumbnail_url = get_the_post_thumbnail_url($post, 'thumbnail');
			        

			        ?>
			        <div class="col-6 col-sm-4 col-md-3">
			        <div class="card mb-4">
	   							<a href="<?php echo esc_url($this->gen_url_storiepost($post_name));?>">  
	   								<img loading="lazy" style="object-fit: cover" src="<?php echo esc_url($post_thumbnail_url);  ?>" class="card-img-top _img-fluid" alt="<?php echo esc_html($post_title) ?>" width="320" height="160"></a><div class="card-body">    <h2 class="card-title h5"><a href="<?php echo esc_url($this->gen_url_storiepost($post_name));?>"><?php echo esc_html($post_title) ?></a></h2>    <p class="card-text"></p></div>
	   				</div>
	   			</div>
	   							
			        <?php
			        
			    }
			    wp_reset_postdata();
			} else {
			    echo 'No posts found.';
			}
	   						 ?>
	   						
	   					</div>
	   				</div>
	   				<?php 
	   				echo '<nav aria-label="Page navigation">';
					    echo '<ul class="pagination justify-content-center" style="width:auto">';
					    echo paginate_links(array(
					        'total'     => $query->max_num_pages,
					        'current'   => max(1, get_query_var('paged')),
					        'prev_text' => '«',
					        'next_text' => '»',
					    ));
					    echo '</ul></nav>';

					    wp_reset_postdata();
	   				 ?> 
	   				   </div><div class="text-center mt-5 mb-0">

	   							<div style="overflow: auto; background: indigo; height: auto;" class="text-center _rounded py-5"><span class="lead text-white">AI auto tool - Stories</span><br>
	   									<a target="_blank" href="https://aiautotool.com"><img loading="lazy" style="max-height: 80px; margin-top: 1rem" alt="Web Stories by aiautotool.com" src=""></a></div></div>  

</body></html>
<?php
		}

		public function gen_url_storiepost($postname) {
		    $current_domain = home_url('/');

		    $full_url = trailingslashit($current_domain) . 'aiwebstories/' . $postname.'/';

		    return esc_url($full_url);
		}
		public function add_custom_rewrite_rule() {
		    
		    add_rewrite_rule('^aiwebstories$', 'index.php?custom_aiwebstories=1', 'top');
		     add_rewrite_rule(
		        '^aiwebstories/page/([0-9]+)/?$',
		        'index.php?custom_aiwebstories=1&paged=$matches[1]',
		        'top'
		    );

		    add_rewrite_rule('^aiwebstories/feed$', 'index.php?custom_aiwebstories=1&aiwebstories_feed=1', 'top');
		    add_rewrite_rule(
			        '^aiwebstories/([^/]+)/?$',
			        'index.php?custom_aiwebstories=1&postname=$matches[1]',
			        'top'
			    );
		    if( !get_option($this->aiautotool_rewriter_flushed) ) {

				flush_rewrite_rules(false);
				update_option($this->aiautotool_rewriter_flushed, 1);

			}
		    
		}
		public function custom_rewrite_query_vars($query_vars) {
		    $query_vars[] = 'custom_aiwebstories';
		    $query_vars[] = 'postname';
		    $query_vars[] = 'aiwebstories_feed';
		    // print_r($query_vars);
		    return $query_vars;
		}
		
		public function custom_rewrite_rule_template($template) {
		    if (get_query_var('custom_aiwebstories')) {
		    	
		    	if(get_query_var('aiwebstories_feed')){
		    		$this->custom_feed_template();
			            exit;
		    	}else{
		    		$postname = get_query_var('postname');

			    	if ($postname) {
			            $this->display_theme_for_postname($postname);
			            exit;
			        }else{
			        	 $this->custom_rewrite_rule_handler();
			        	exit;
			        }
		    	}
		    	
		       
		    }
		    return $template;
		}

		public function display_theme_for_postname($postname) {
		    $page = get_page_by_path($postname,OBJECT,'post');
		    if ($page) {
		        // Lấy thông tin chi tiết của bài viết
		        $post_id = $page->ID;
		        $post_title = get_the_title($post_id);
		        $post_url = get_permalink($post_id);
		        // $post_content = get_post_field('post_content', $post_id);
		        $post_content = get_the_content(null, false, $post_id);
		        $url_thumb = get_the_post_thumbnail_url($post_id, 'thumbnail');

		        $post_date = get_post_time('Y-m-d\TH:i:sP', true, $post_id); 
        		$post_author = get_the_author_meta('display_name', get_post_field('post_author', $post_id));

		       
		        $image_urls = array();
		        $paragraphs = array();

		        $dom = new DOMDocument();
		        libxml_use_internal_errors(true); 
		        $dom->loadHTML($post_content);
		        libxml_use_internal_errors(false); 

		        $images = $dom->getElementsByTagName('img');
		        $image_urls = array();

		        foreach ($images as $image) {
		            $src = $image->getAttribute('src');
		            if ($src) {
		                $image_urls[] = $src;
		            }
		        }
		        if ($url_thumb=='') {
		        	$url_thumb = $image_urls[0];
		        }
		        
		        $contenttext = strip_tags($post_content);
		        $paragraphs = explode('.',$contenttext);
		      
		        $article_body_content = implode("\n", $paragraphs);
		         $article_body_unicode = json_encode($article_body_content, JSON_UNESCAPED_UNICODE);

		    
		    ?><!DOCTYPE html>
<html amp lang="en-US">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="follow, index, max-snippet:-1, max-video-preview:-1, max-image-preview:large"/>
<title><?php echo esc_html($post_title);?></title>
<meta name="description" content="">
<meta name="theme-color" content="#ff0000">
<meta property="og:title" content="<?php echo esc_html($post_title);?>">
<meta property="og:description" content="">
<meta property="og:image" content="<?php echo esc_url($url_thumb);?>">
<meta property="og:url" content="<?php echo esc_url($this->gen_url_storiepost($postname));?>">
<meta property="og:type" content="article">
<meta property="og:site_name" content="<?php echo esc_html($post_author); ?>">
<meta property="og:locale" content="en-US">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:title" content="<?php echo esc_html($post_title);?>">
<meta property="twitter:description" content=""><meta property="twitter:image" content="<?php echo esc_url($url_thumb);?>">
<meta property="twitter:url" content="<?php echo esc_url($this->gen_url_storiepost($postname));?>">
<meta property="twitter:site" content="<?php echo esc_html($post_author); ?>">
<meta property="twitter:creator" content="<?php echo esc_html($post_author); ?>">
<meta property="twitter:locale" content="en-US">
<meta itemprop="name" content="<?php echo esc_html($post_title);?>">
<meta itemprop="description" content="">
<meta itemprop="image" content="<?php echo esc_url($url_thumb);?>">
<meta itemprop="url" content="<?php echo esc_url($this->gen_url_storiepost($postname));?>">
<meta itemprop="author" content="<?php echo esc_html($post_author); ?>">
<meta itemprop="publisher" content="<?php echo esc_html($post_author); ?>">
<meta itemprop="inLanguage" content="en-US">
<link rel="canonical" href="<?php echo esc_url($this->gen_url_storiepost($postname));?>">
<link rel="icon" href="<?php echo $this->setting['logo']; ?>">
		    	<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes
    -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes
    -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript><style amp-custom>.amphtml-cap span,amp-img:after{display:block;position:absolute;left:0}amp-story-page{background:#000}amp-img img{width:100%;height:auto;margin:0;object-fit:cover}amp-img:after{background:linear-gradient(180deg,#fff0,#000);content:"";height:36%;pointer-events:none;bottom:0;width:100%}.amphtml-cap{position:relative;width:100%;height:100%}.amphtml-cap span{padding:15px;right:0;bottom:80px;color:#fff;font-size:24px;line-height:1.2}#page-1 amp-img img{height:auto;min-height:0;margin:0}#page-1 amp-img:after{height:58.1436%;background: linear-gradient(180deg,#fff0,#ff5722)}#page-2 amp-img img{height:auto;min-height:0;margin:0}#page-2 amp-img:after{height:64.0008%;background: linear-gradient(180deg,#fff0,#009688)}#page-3 amp-img img{height:auto;min-height:0;margin:0}#page-3 amp-img:after{height:53.9568%;background: linear-gradient(180deg,#fff0,#795548)}#page-4 amp-img img{height:auto;min-height:0;margin:0}#page-4 amp-img:after{height:49.5%;background: linear-gradient(180deg,#fff0,#4caf50)}#page-5 amp-img img{height:auto;min-height:0;margin:0}#page-5 amp-img:after{height:54%;background: linear-gradient(180deg,#fff0,#4caf50)}#page-6 amp-img img{height:auto;min-height:0;margin:0}#page-6 amp-img:after{height:54%;background: linear-gradient(180deg,#fff0,#2196f3)}#page-7 amp-img img{height:auto;min-height:0;margin:0}#page-7 amp-img:after{height:57.6%;background: linear-gradient(180deg,#fff0,#ff9800)}#page-8 amp-img img{height:auto;min-height:0;margin:0}#page-8 amp-img:after{height:68.5728%;background: linear-gradient(180deg,#fff0,#cddc39)}#page-9 amp-img img{height:auto;min-height:0;margin:0}#page-9 amp-img:after{height:51.5232%;background: linear-gradient(180deg,#fff0,#00bcd4)}#page-10 amp-img img{height:auto;min-height:0;margin:0}#page-10 amp-img:after{height:64.0008%;background: linear-gradient(180deg,#fff0,#4caf50)}#page-11 amp-img img{height:auto;min-height:0;margin:0}#page-11 amp-img:after{height:47.9988%;background: linear-gradient(180deg,#fff0,#8bc34a)}#page-12 amp-img img{height:auto;min-height:0;margin:0}#page-12 amp-img:after{height:36%;background: linear-gradient(180deg,#fff0,#ff5722)}#page-13 amp-img img{height:auto;min-height:0;margin:0}#page-13 amp-img:after{height:43.2%;background: linear-gradient(180deg,#fff0,#00bcd4)}#page-14 amp-img img{height:auto;min-height:0;margin:0}#page-14 amp-img:after{height:54.396%;background: linear-gradient(180deg,#fff0,#f44336)}#page-15 amp-img img{height:auto;min-height:0;margin:0}#page-15 amp-img:after{height:36%;background: linear-gradient(180deg,#fff0,#ff5722)}#page-16 amp-img img{height:auto;min-height:0;margin:0}#page-16 amp-img:after{height:52.362%;background: linear-gradient(180deg,#fff0,#8bc34a)}#page-17 amp-img img{height:auto;min-height:0;margin:0}#page-17 amp-img:after{height:71.3232%;background: linear-gradient(180deg,#fff0,#f44336)}#page-18 amp-img img{height:auto;min-height:0;margin:0}#page-18 amp-img:after{height:54.4068%;background: linear-gradient(180deg,#fff0,#2196f3)}#page-19 amp-img img{height:auto;min-height:0;margin:0}#page-19 amp-img:after{height:64.0008%;background: linear-gradient(180deg,#fff0,#00bcd4)}#page-20 amp-img img{height:auto;min-height:0;margin:0}#page-20 amp-img:after{height:47.0412%;background: linear-gradient(180deg,#fff0,#ffc107)}#page-21 amp-img img{height:auto;min-height:0;margin:0}#page-21 amp-img:after{height:52.758%;background: linear-gradient(180deg,#fff0,#e91e63)}#page-22 amp-img img{height:auto;min-height:0;margin:0}#page-22 amp-img:after{height:64.0008%;background: linear-gradient(180deg,#fff0,#ffc107)}#page-23 amp-img img{height:auto;min-height:0;margin:0}#page-23 amp-img:after{height:57.5064%;background: linear-gradient(180deg,#fff0,#9c27b0)}#page-24 amp-img img{height:auto;min-height:0;margin:0}#page-24 amp-img:after{height:63.4788%;background: linear-gradient(180deg,#fff0,#03a9f4)}#page-25 amp-img img{height:auto;min-height:0;margin:0}#page-25 amp-img:after{height:47.2896%;background: linear-gradient(180deg,#fff0,#607d8b)}#page-27 amp-img img{height:auto;min-height:0;margin:0}#page-27 amp-img:after{height:54%;background: linear-gradient(180deg,#fff0,#9c27b0)}#page-28 amp-img img{height:auto;min-height:0;margin:0}#page-28 amp-img:after{height:47.9988%;background: linear-gradient(180deg,#fff0,#009688)}#page-29 amp-img img{height:auto;min-height:0;margin:0}#page-29 amp-img:after{height:36%;background: linear-gradient(180deg,#fff0,#795548)}#page-30 amp-img img{height:auto;min-height:0;margin:0}#page-30 amp-img:after{height:64.0008%;background: linear-gradient(180deg,#fff0,#009688)}#page-31 amp-img img{height:auto;min-height:0;margin:0}#page-31 amp-img:after{height:52.9776%;background: linear-gradient(180deg,#fff0,#f44336)}#page-32 amp-img img{height:auto;min-height:0;margin:0}#page-32 amp-img:after{height:51.9228%;background: linear-gradient(180deg,#fff0,#ffeb3b)}#page-33 amp-img img{height:auto;min-height:0;margin:0}#page-33 amp-img:after{height:36%;background: linear-gradient(180deg,#fff0,#cddc39)}#page-34 amp-img img{height:auto;min-height:0;margin:0}#page-34 amp-img:after{height:36%;background: linear-gradient(180deg,#fff0,#9e9e9e)}#page-35 amp-img img{height:auto;min-height:0;margin:0}#page-35 amp-img:after{height:64.422%;background: linear-gradient(180deg,#fff0,#e91e63)}#page-36 amp-img img{height:auto;min-height:0;margin:0}#page-36 amp-img:after{height:42.9444%;background: linear-gradient(180deg,#fff0,#4caf50)}#page-37 amp-img img{height:auto;min-height:0;margin:0}#page-37 amp-img:after{height:54.4176%;background: linear-gradient(180deg,#fff0,#e91e63)}#page-38 amp-img img{height:auto;min-height:0;margin:0}#page-38 amp-img:after{height:64.0008%;background: linear-gradient(180deg,#fff0,#9c27b0)}#page-39 amp-img img{height:auto;min-height:0;margin:0}#page-39 amp-img:after{height:58.1832%;background: linear-gradient(180deg,#fff0,#00bcd4)}#page-40 amp-img img{height:auto;min-height:0;margin:0}#page-40 amp-img:after{height:52.362%;background: linear-gradient(180deg,#fff0,#9e9e9e)}#page-41 amp-img img{height:auto;min-height:0;margin:0}#page-41 amp-img:after{height:53.2728%;background: linear-gradient(180deg,#fff0,#ff9800)}#page-42 amp-img img{height:auto;min-height:0;margin:0}#page-42 amp-img:after{height:63.6012%;background: linear-gradient(180deg,#fff0,#e91e63)}#page-43 amp-img img{height:auto;min-height:0;margin:0}#page-43 amp-img:after{height:53.9316%;background: linear-gradient(180deg,#fff0,#ff9800)}#page-44 amp-img img{height:auto;min-height:0;margin:0}#page-44 amp-img:after{height:54.0828%;background: linear-gradient(180deg,#fff0,#f44336)}#page-45 amp-img img{height:auto;min-height:0;margin:0}#page-45 amp-img:after{height:49.2912%;background: linear-gradient(180deg,#fff0,#3f51b5)}#page-46 amp-img img{height:auto;min-height:0;margin:0}#page-46 amp-img:after{height:47.9988%;background: linear-gradient(180deg,#fff0,#3f51b5)}#page-47 amp-img img{height:auto;min-height:0;margin:0}#page-47 amp-img:after{height:60.48%;background: linear-gradient(180deg,#fff0,#8bc34a)}#page-48 amp-img img{height:auto;min-height:0;margin:0}#page-48 amp-img:after{height:64.0008%;background: linear-gradient(180deg,#fff0,#2196f3)}#page-49 amp-img img{height:auto;min-height:0;margin:0}#page-49 amp-img:after{height:49.8996%;background: linear-gradient(180deg,#fff0,#cddc39)}#page-50 amp-img img{height:auto;min-height:0;margin:0}#page-50 amp-img:after{height:54%;background: linear-gradient(180deg,#fff0,#3f51b5)}#page-51 amp-img img{height:auto;min-height:0;margin:0}#page-51 amp-img:after{height:62.4276%;background: linear-gradient(180deg,#fff0,#4caf50)}#page-52 amp-img img{height:auto;min-height:0;margin:0}#page-52 amp-img:after{height:63.6012%;background: linear-gradient(180deg,#fff0,#f44336)}#page-53 amp-img img{height:auto;min-height:0;margin:0}#page-53 amp-img:after{height:54%;background: linear-gradient(180deg,#fff0,#607d8b)}#page-54 amp-img img{height:auto;min-height:0;margin:0}#page-54 amp-img:after{height:51.57%;background: linear-gradient(180deg,#fff0,#009688)}#page-56 amp-img img{height:auto;min-height:0;margin:0}#page-56 amp-img:after{height:77.1444%;background: linear-gradient(180deg,#fff0,#3f51b5)}#page-57 amp-img img{height:auto;min-height:0;margin:0}#page-57 amp-img:after{height:45.288%;background: linear-gradient(180deg,#fff0,#ffeb3b)}#page-59 amp-img img{height:auto;min-height:0;margin:0}#page-59 amp-img:after{height:52.056%;background: linear-gradient(180deg,#fff0,#cddc39)}#page-60 amp-img img{height:auto;min-height:0;margin:0}#page-60 amp-img:after{height:54.3384%;background: linear-gradient(180deg,#fff0,#e91e63)}#page-61 amp-img img{height:auto;min-height:0;margin:0}#page-61 amp-img:after{height:54%;background: linear-gradient(180deg,#fff0,#2196f3)}#page-62 amp-img img{height:auto;min-height:0;margin:0}#page-62 amp-img:after{height:54.054%;background: linear-gradient(180deg,#fff0,#673ab7)}#page-63 amp-img img{height:auto;min-height:0;margin:0}#page-63 amp-img:after{height:64.0008%;background: linear-gradient(180deg,#fff0,#8bc34a)}#page-64 amp-img img{height:auto;min-height:0;margin:0}#page-64 amp-img:after{height:54%;background: linear-gradient(180deg,#fff0,#009688)}#page-65 amp-img img{height:auto;min-height:0;margin:0}#page-65 amp-img:after{height:36%;background: linear-gradient(180deg,#fff0,#ff5722)}.bg-image {  height: 100px; background-position: center; background-repeat: no-repeat; background-size: cover; }.blur { background: #ffffffbb;backdrop-filter: blur(10px);height: 100vh;width: 100vw;position: absolute;top: 0;left: 0;}</style>
		    <script async src="https://cdn.ampproject.org/v0.js"></script>
		    <script async custom-element="amp-story" src="https://cdn.ampproject.org/v0/amp-story-1.0.js"></script>

		    <?php 
		     echo '<script type="application/ld+json">';
        echo json_encode(array(
            '@context'          => 'http://schema.org',
            '@type'             => 'Article',
            'mainEntityOfPage'  => array(
                '@type' => 'WebPage',
                'url'   => esc_url($post_url),
                'name'  => esc_html($post_title),
            ),
            'headline'          => esc_html($post_title),
            'datePublished'     => $post_date,
            'dateModified'      => $post_date,
            'author'            => array(
                '@type' => 'Person',
                'name'  => esc_html($post_author),
                'url'   => get_author_posts_url(get_the_author_meta('ID', $post_id)),
            ),
            'publisher'         => array(
                '@type' => 'Organization',
                'name'  => get_bloginfo('name'),
            ),
            'description'       => '',
            'image'             => array(
                '@type' => 'ImageObject',
                'url'   => esc_url($url_thumb),
            ),
            'articleBody'       => strip_tags($article_body_unicode),
        ), JSON_UNESCAPED_UNICODE);
        echo '</script>';
		     ?>
		 <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
		</head><body><amp-story standalone title="<?php echo esc_html($post_title);?>" publisher="<?php echo esc_html($post_author); ?>" publisher-logo-src="<?php echo $this->setting['logo']; ?>" poster-portrait-src="<?php echo esc_url($url_thumb);?>" background-audio="<?php echo $this->setting['mp31']; ?>">
				<amp-story-page id="cover" class="bg-image bg-image-cover page">
					<amp-story-grid-layer template="vertical" class="blur">
						<amp-img src="<?php echo esc_url($url_thumb);?>" width="100" height="100" layout="responsive" object-fit="cover" style="border-radius: 1rem">
						</amp-img>
						<h1 style="color: #fff;"><?php echo esc_html($post_title);?></h1>
					</amp-story-grid-layer>
				</amp-story-page>
				<?php 
				foreach ($paragraphs as $index => $paragraph) {
					if($paragraph!=''){
						$img = $url_thumb;
				    	 	if(isset($image_urls[$index])){
				    	 		$img = $image_urls[$index];
				    	 	}
						?>
						<amp-story-page id="page-<?php echo esc_attr($index);  ?>" auto-advance-after="7s">
					        <amp-story-grid-layer template="vertical">
					            <amp-img layout="fill" src="<?php echo esc_url($img); ?>" alt="<?php echo esc_html($paragraph); ?> - <?php echo esc_html($post_author); ?>"></amp-img>
					        </amp-story-grid-layer>
					        <amp-story-grid-layer template="vertical" class="amphtml-cap">
					            <span><?php echo esc_html($paragraph); ?></span>
					        </amp-story-grid-layer>
					        <amp-story-page-outlink layout="nodisplay">
					            <a href="<?php echo esc_url($post_url); ?>" rel="">Read more</a>
					        </amp-story-page-outlink>
					    </amp-story-page>
						<?php
		           
		            }
		        }
				 ?>
				<amp-story-page id="conclusion" auto-advance-after="7s">
					        <amp-story-grid-layer template="vertical">
					            <amp-img layout="fill" src="<?php echo esc_url($url_thumb); ?>" alt=""></amp-img>
					        </amp-story-grid-layer>
					        <amp-story-grid-layer template="vertical" class="amphtml-cap">
					            <span style="font-weight: 400; position: fixed; bottom: 2.5rem; width: 100%; left: 0; font-size: 1.2rem; display: inline-block; vertical-align: bottom;">Visit our site and see all other available articles!</span>
					        </amp-story-grid-layer>
					        <amp-story-page-outlink layout="nodisplay" theme="custom" cta-accent-element="background" cta-accent-color="#fff">
					            <a href="<?php echo esc_url($post_url); ?>" rel="">Read more</a>
					        </amp-story-page-outlink>
					    </amp-story-page>
	<?php if(isset($this->setting['analytic'])&&$this->setting['analytic']!=''){
		?>
			<amp-analytics type="gtag" data-credentials="include">
              <script type="application/json">
                {
                  "vars" : {
                    "gtag_id": "<?php echo esc_html($this->setting['analytic']); ?>",
                    "config" : {
                      "G-VFCXCWFEKK": { "groups": "default" }
                    }
                  }
                }
            </script>
            </amp-analytics>
		<?php
	} ?>

    <amp-story-social-share layout="nodisplay" hidden="hidden">
    <script type="application/json">
        {
            "shareProviders": [
                {
                    "provider": "facebook"
                },
                {
                    "provider": "twitter"
                }, 
                {
                    "provider": "linkedin"
                }, 
                {
                    "provider": "email"
                }, 
                {
                    "provider": "system"
                }
            ]
        }
    </script>
</amp-story-social-share>

				</body>
				</html>
					<?php
		    
		    } else {
		        echo '<p>Post not found!</p>';
		    }
		    
		}


		public function custom_feed_template() {
        header('Content-Type: application/rss+xml; charset=' . get_option('blog_charset'), true);
		$more = 1;

		echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . '>';
		?><rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/">
<channel>
    <title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
    <atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
    <link><?php bloginfo_rss('url') ?></link>
    <description><?php bloginfo_rss("description") ?></description>
    <lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
    <language>en</language>
    <sy:updatePeriod><?php echo apply_filters('rss_update_period', 'daily'); ?></sy:updatePeriod>
    <sy:updateFrequency><?php echo apply_filters('rss_update_frequency', '1'); ?></sy:updateFrequency>
    <?php do_action('rss2_head'); ?>
    <?php
   
    while (have_posts()) : the_post();
    	$post_name = get_post_field('post_name', $post->ID);
    	$urllink = esc_url($this->gen_url_storiepost($post_name));
    	$description = get_the_excerpt();
    $content = get_the_content();
        ?>
        <item>
            <title>Stories <?php the_title_rss(); ?></title>
            <link><?php echo $urllink; ?></link>
            <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
            <guid isPermaLink="false"><?php echo $urllink; ?></guid>
            <description><![CDATA[<?php echo $description; ?>]]></description>
            <content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded>
            <slash:comments>0</slash:comments>
        </item>
        <?php
    endwhile;
    ?>
</channel>
</rss>
		<?php
    	}
}

