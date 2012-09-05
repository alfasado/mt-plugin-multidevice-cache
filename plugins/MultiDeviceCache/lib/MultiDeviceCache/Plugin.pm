package MultiDeviceCache::Plugin;
use strict;
use File::Spec;

sub _build_file {
    my ( $cb, %args ) = @_;
    require MT::FileMgr;
    my $fmgr = MT::FileMgr->new( 'Local' );
    my $app = MT->instance();
    my $file = $args{ File };
    my $blog = $args{ Blog };
    my $dir = $app->config( 'MultiDeviceCacheDir' );
    $dir = File::Spec->catdir( $blog->site_path, $dir );
    require Digest::MD5;
    $file = Digest::MD5::md5_hex( $file );
    my $pc = File::Spec->catfile( $dir, 'pc_' . $file );
    my $sp = File::Spec->catfile( $dir, 'sp_' . $file );
    my $fp = File::Spec->catfile( $dir, 'fp_' . $file );
    if ( $fmgr->exists( $pc ) ) {
        $fmgr->delete( $pc );
    }
    if ( $fmgr->exists( $sp ) ) {
        $fmgr->delete( $sp );
    }
    if ( $fmgr->exists( $fp ) ) {
        $fmgr->delete( $fp );
    }
    return 1;
}

1;