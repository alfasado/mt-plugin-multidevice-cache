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
    my $pc = 'pc_' . $file;
    my $sp = 'sp_' . $file;
    my $fp = 'fp_' . $file;
    my @files = __get_children_filenames( $dir, "/_$file/" );
    for my $cache ( @files ) {
        if ( $fmgr->exists( $cache ) ) {
            $fmgr->delete( $cache );
        }
    }
    return 1;
}

sub __get_children_filenames {
    my ( $directory, $pattern ) = @_;
    my @wantedFiles;
    require File::Find;
    if ( $pattern ) {
        if ( $pattern =~ m!^(/)(.+)\1([A-Za-z]+)?$! ) {
            $pattern = $2;
            MT->log($pattern);
            if ( my $opt = $3 ) {
                $opt =~ s/[ge]+//g;
                $pattern = "(?$opt)" . $pattern;
            }
            my $regex = eval { qr/$pattern/ };
            if ( defined $regex ) {
                my $command = 'File::Find::find ( sub { push ( @wantedFiles, $File::Find::name ) if ( /' . $pattern. '/ ) && -f; }, $directory );';
                eval $command;
                if ( $@ ) {
                    return undef;
                }
            } else {
                return undef;
            }
        }
    } else {
        File::Find::find ( sub { push ( @wantedFiles, $File::Find::name ) unless (/^\./) || ! -f; }, $directory );
    }
    return @wantedFiles;
}

1;