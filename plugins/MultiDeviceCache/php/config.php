<?php
class MultiDeviceCache extends MTPlugin {
    var $app;
    var $registry = array(
        'name' => 'MultiDeviceCache',
        'id'   => 'MultiDeviceCache',
        'key'  => 'multidevicecache',
        'author_name' => 'Alfasado Inc.',
        'author_link' => 'http://alfasado.net/',
        'version'     => '0.1',
        'description' => 'Multi device cache for DynamicMTML.',
        'config_settings' => array(
            'EnableMultiDeviceCachePC' => array( 'default' => '1' ),
            'EnableMultiDeviceCacheSP' => array( 'default' => '1' ),
            'EnableMultiDeviceCacheFP' => array( 'default' => '0' ),
            'MultiDeviceCacheTabletIsPC' => array( 'default' => '1' ),
            'MultiDeviceCacheDir' => array( 'default' => 'multidevicecache' ),
        ),
        'callbacks' => array(
            'pre_resolve_url' => 'callback_pre_resolve_url',
            'post_return' => 'callback_post_return',
        ),
    );

    function callback_pre_resolve_url ( $mt, &$ctx, &$args ) {
        $app = $ctx->stash( 'bootstrapper' );
        $path = $this->__get_cache_path( $app, $ctx );
        if ( $path && is_file( $path ) ) {
            echo file_get_contents( $path );
            exit();
        }
    }

    function callback_post_return ( $mt, &$ctx, &$args, &$content ) {
        $app = $ctx->stash( 'bootstrapper' );
        $path = $this->__get_cache_path( $app, $ctx );
        if (! file_exists( $path ) ) {
            $app->write2file( $path, $content );
        }
    }

    function __get_cache_path ( $app, $ctx ) {
        require_once( 'dynamicmtml.util.php' );
        $blog = $ctx->stash( 'blog' );
        $request = $app->stash( 'file' );
        $chache_dir = $blog->site_path() . DIRECTORY_SEPARATOR . $app->config( 'MultiDeviceCacheDir' );
        $fp = $app->get_agent( 'Keitai' );
        $sp = $app->get_agent( 'Smartphone' );
        $pc = $app->get_agent();
        if ( $pc == 'PC' ) {
            $pc = 1;
            $ua = 'pc';
        } else {
            $pc = 0;
        }
        if ( $sp ) {
            $ua = 'sp';
            if ( $app->config( 'MultiDeviceCacheTabletIsPC' ) ) {
                if ( get_agent( 'tablet' ) ) {
                    $pc = 1;
                    $sp = 0;
                    $ua = 'pc';
                }
            }
            if (! $app->config( 'EnableMultiDeviceCachePC' ) ) {
                return '';
            }
        }
        if ( $pc ) {
            if (! $app->config( 'EnableMultiDeviceCachePC' ) ) {
                return '';
            }
        } elseif ( $fp ) {
            $ua = 'fp';
            if (! $app->config( 'EnableMultiDeviceCacheFP' ) ) {
                return '';
            }
        }
        return $chache_dir . DIRECTORY_SEPARATOR . $ua . '_' . md5( $request );
    }
}

?>