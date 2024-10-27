<?php
defined('ABSPATH') or die();
class aiautotool_SSL_active extends rendersetting{
	public  $active = true;
    public  $active_option_name = 'aiautotool_SSL_active_active';

	public function __construct() {

		 $this->active = get_option($this->active_option_name, true);
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
    	$siteurl = get_option('siteurl');

		// Lấy giá trị của biến option 'home'
		$home = get_option('home');

		// Kiểm tra xem $siteurl có chứa 'https' không
		if (strpos($siteurl, 'https') === false) {
		    // Nếu không có, thay thế 'http://' bằng 'https://'
		    $siteurl = str_replace('http://', 'https://', $siteurl);

		    // Lưu lại giá trị mới cho biến option 'siteurl'
		    update_option('siteurl', $siteurl);
		}

		// Kiểm tra xem $home có chứa 'https' không
		if (strpos($home, 'https') === false) {
		    // Nếu không có, thay thế 'http://' bằng 'https://'
		    $home = str_replace('http://', 'https://', $home);

		    // Lưu lại giá trị mới cho biến option 'home'
		    update_option('home', $home);
		}
    	$this->run();
    }

    public function render_setting() {

    }
    public function render_tab_setting() {
        
    }

    public function render_feature(){
        $autoToolBox = new AutoToolBox("SSL Active Fix", "Active SSL Simple", "#", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

        echo $autoToolBox->generateHTML();
        
    }


	public function run() {
		if ( !$this->isSsl() && $this->isSslToNonSslProxy() ) {
			$_SERVER[ 'HTTPS' ] = 'on';
			add_action( 'shutdown', array( $this, 'maintainPluginLoadPosition' ) );
		}
		
	}

	/**
	 * @return bool
	 */
	private function isSsl() {
		return function_exists( 'is_ssl' ) && is_ssl();
	}

	/**
	 * @return bool
	 */
	private function isSslToNonSslProxy() {
		$bIsProxy = false;

		$aServerKeys = array( 'HTTP_CF_VISITOR', 'HTTP_X_FORWARDED_PROTO' );
		foreach ( $aServerKeys as $sKey ) {
			if ( isset( $_SERVER[ $sKey ] ) && ( strpos( $_SERVER[ $sKey ], 'https' ) !== false ) ) {
				$bIsProxy = true;
				break;
			}
		}

		return $bIsProxy;
	}

	/**
	 * Sets this plugin to be the first loaded of all the plugins.
	 */
	public function maintainPluginLoadPosition() {
		$sBaseFile = plugin_basename( __FILE__ );
		$nLoadPosition = $this->getActivePluginLoadPosition( $sBaseFile );
		if ( $nLoadPosition > 1 ) {
			$this->setActivePluginLoadPosition( $sBaseFile, 0 );
		}
	}

	/**
	 * @param string $sPluginFile
	 * @return int
	 */
	private function getActivePluginLoadPosition( $sPluginFile ) {
		$sOptionKey = is_multisite() ? 'active_sitewide_plugins' : 'active_plugins';
		$aActive = get_option( $sOptionKey );
		$nPosition = -1;
		if ( is_array( $aActive ) ) {
			$nPosition = array_search( $sPluginFile, $aActive );
			if ( $nPosition === false ) {
				$nPosition = -1;
			}
		}
		return $nPosition;
	}

	/**
	 * @param string $sPluginFile
	 * @param int    $nDesiredPosition
	 */
	private function setActivePluginLoadPosition( $sPluginFile, $nDesiredPosition = 0 ) {

		$aActive = $this->setArrayValueToPosition( get_option( 'active_plugins' ), $sPluginFile, $nDesiredPosition );
		update_option( 'active_plugins', $aActive );

		if ( is_multisite() ) {
			$aActive = $this->setArrayValueToPosition( get_option( 'active_sitewide_plugins' ), $sPluginFile, $nDesiredPosition );
			update_option( 'active_sitewide_plugins', $aActive );
		}
	}

	/**
	 * @param array $aSubjectArray
	 * @param mixed $mValue
	 * @param int   $nDesiredPosition
	 * @return array
	 */
	private function setArrayValueToPosition( $aSubjectArray, $mValue, $nDesiredPosition ) {

		if ( $nDesiredPosition < 0 || !is_array( $aSubjectArray ) ) {
			return $aSubjectArray;
		}

		$nMaxPossiblePosition = count( $aSubjectArray ) - 1;
		if ( $nDesiredPosition > $nMaxPossiblePosition ) {
			$nDesiredPosition = $nMaxPossiblePosition;
		}

		$nPosition = array_search( $mValue, $aSubjectArray );
		if ( $nPosition !== false && $nPosition != $nDesiredPosition ) {

			// remove existing and reset index
			unset( $aSubjectArray[ $nPosition ] );
			$aSubjectArray = array_values( $aSubjectArray );

			// insert and update
			// http://stackoverflow.com/questions/3797239/insert-new-item-in-array-on-any-position-in-php
			array_splice( $aSubjectArray, $nDesiredPosition, 0, $mValue );
		}

		return $aSubjectArray;
	}
}
