# Credit #

This script was created by meltingwax.

# Instructions #

**This will only work if kareha is in text mode**

Like W2K, K2K works on each board individually.  First create a file using the code below.

Edit the file, changing BOARD\_NAME to the directory of the board which is currently being converted.  You will most likely not need to change DIR, unless you have also change it in the kareha config.

Upload the file to the directory which contains kareha.pl

Run the script and copy the output.  Insert this using phpMyAdmin or an equivalent.

This has only been tested a few times, so please report any positive or negative feedback in /c/.


# k2k.pl #

```
#!/usr/bin/perl

use Data::Dumper;

use constant BOARD_NAME => 'lounge';
use constant DIR => 'res';

print "Content-type: text/plain;charset=utf8\n\n";

##-------------------------------------------------

my @threads;

opendir(RES, DIR);
map { push(@threads, read_thread($_)) } grep { /\.html$/ && -f DIR . "/$_" } readdir(RES);
close(RES);


print "INSERT INTO `posts_" . BOARD_NAME . "` (
  `id` ,
  `parentid` ,
  `name` ,
  `tripcode` ,
  `email` ,
  `message` ,
  `subject` ,
  `postedat` ,
  `lastbumped` ,
  `locked`
)
VALUES\n";


our $board_name = BOARD_NAME;

my  $id = 1;
foreach my $thread ( @threads ) {
	my $parentid = $id;
	
	my $firstpost = ${shift @{$thread->{posts}}};
	$firstpost->{message} =~ s/\"(.*?)\/kareha\.pl\/([0-9]+)\/([0-9]+)/\"\/read\.php\/$board_name\/$parentid\/$3/;
	
	print "("
	. "$id, "
	. "0, "
	. "'$firstpost->{name}', "
	. "'$firstpost->{trip}', "
	. "'$firstpost->{email}', "
	. "'$firstpost->{message}', "
	. "'$thread->{meta}->{title}', "
	. "$thread->{time}, "
	. "$thread->{meta}->{lasthit}, ";
	if ( $thread->{meta}->{closed} ) {
		print "1";
	}
	else {
		print "0";
	}
	print "), \n";	
	
	$id++;
		
	# loop for replies
	while ( @{$thread->{posts}} ) {
		my $reply = ${shift @{$thread->{posts}}};
		
		$reply->{message} =~ s/\"(.*?)\/kareha\.pl\/([0-9]+)\/([0-9]+)/\"\/read\.php\/$board_name\/$parentid\/$3/;
		
		print "($id, "
		. "$parentid, "
		. "'$reply->{name}', "
		. "'$reply->{trip}', "
		. "'$reply->{email}', "
		. "'$reply->{message}', "
		. "'', "
		. "$thread->{time}, "
		. "0, "
		. "0";
		
		# last post of last thread
		if ( $thread == $threads[ scalar(@threads) - 1] && scalar @{$thread->{posts}} == 0 ) {
			print ") \n";
		}
		else {
			print "), \n";
		}
		$id++;
	} # end of reply loop
	
	print "\n";
} # end of thread loop

print "; ALTER TABLE `posts_" . BOARD_NAME . "` AUTO_INCREMENT = $id";


sub read_thread($) {
    my $filename = shift;

    my $thread = {
        time => 0,
        posts => [],
        meta => (),
    };

    my $time = $filename;
    $time =~ s/\.html$//;
    $thread->{time} = $time;

    open(FILE, DIR . "/$filename");

    foreach my $line ( <FILE> ) {
        chomp $line;

        my $post;
        
        # thread "meta" info
        if ( $line =~ /^\<!--(.*)--\>$/ ) {
            %{$thread->{meta}} =  %{eval $1};
            
            foreach ( keys %{$thread->{meta}} ) {
            	$thread->{meta}->{$_} = filter($thread->{meta}->{$_});
            }
        }
        
        # reply
        elsif ( $line =~ /^\<div class=\"reply\"/ ) {
            # name email
            if ( $line =~ /\<span class=\"postername\"\>((\<a href=\"mailto\:(.*?)\"(( rel=\"nofollow\")?)\>)?)(.*?)((\<\/a\>)?)\<\/span\>/ ) {  
                $post->{email} = $3 || '';
                $post->{name} = $6;
            }
            else {
                $post->{name} = '<font color=red><b>No message text found</b></font>';
                $post->{email} = '<font color=red><b>error parsing email</b></font>';
            }

            # trip
            if ( $line =~ /\<span class=\"postertrip\">(\<a (.*?))?>(.*?)(\<\/a\>)?\<\/span\>/ ) {
                $post->{trip} = $3;
            }
            elsif ( $line =~ /\<span class=\"postertrip\">(.*?)\<\/span\>/ ) {
                $post->{trip} = $1;
            }
            else {
                $post->{trip} = '';
            }

            # post message
            if ( $line =~ /\<div class=\"aa\"\>(.*?)\<\/div\>/ ) {
                $post->{message} = '<div class="aa">' . $1 . '</div>';
            }
            elsif ( $line =~ /\<div class=\"replytext\"\>(.*?)\<\/div\>/ ) {
                $post->{message} = $1;
            }
            else {
                $post->{message} = '<font color=red><b>No message text found</b></font>';
            }

            $post->{message} =~ s/\<p\>//g;
            $post->{message} =~ s/\<\/p\>//g;
            
            
            foreach ( keys (%{$post}) ) {
            	$post->{$_} = filter($post->{$_});
            }
			 
            push(@{$thread->{posts}}, \$post);
            
        } # end of <div class="reply">
       
    } # end of line loop

    close(FILE);

    return $thread;
    
} # end of get_thread



sub filter($) {
	my $text = shift;
	
	$text =~ s/\\/\\\\/g;
	$text =~ s/'/\\'/g;
	
	return $text;
}
```