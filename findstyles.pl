#!/usr/bin/perl

# script to find all the style classes in an html file

use strict;
use Data::Dumper qw( DumperX );

my $file = 'wikimedia.html';
my %styles = ();

if (-e $file) {
	if (open(HTML, $file)) {
		
		my $holdTerminator = $/;
		undef $/;
		my $contents = <HTML>;			#slurp the whole file
		close HTML;
		$/ = $holdTerminator;
		
		#do some stuff
		my @finds = $contents =~ /class=['|"](.+?)['|"]/g;
		
		foreach my $style (@finds) {
			if ($style =~ m/ /g) {			#check for multiple classes
				my @temp = split(' ',$style);
				foreach my $tmp (@temp) {
					$styles{$tmp} = '';
				}
			}else{
				$styles{$style} = '';
			}
		}
			
		print join("\n",sort(keys %styles));
		
	}else{
		print "Cannot open file because $!\n";
	}
	
}else{
	print "File does not exist\n";
}
