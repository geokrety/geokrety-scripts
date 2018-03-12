#!/usr/bin/perl

sub patch_tdb {
  my ($infile, $fid) = @_;
  my $outfile = "$$.tmp";
  my $buf;

  print STDERR ("Patching TDB file ... ");

  open (INF, "< $infile" ) or die "Error opening input '$infile': $!\n";
  open (OUTF, "> $outfile" ) or die "Error opening output '$outfile': $!\n";
  binmode INF;
  binmode OUTF;

  # read up to 64k from infile into buffer
  read (INF, $buf, 65535) or die "Problem reading: $!\n";

  # unpack buffer into string
  my $hex = unpack( "H*", $buf );

  # replace after position 10 for two/3 bytes
#  substr($hex, 10, 3) = sprintf("%02x", $fid);
  substr($hex, 10, 4) = sprintf("%04x", $fid);

  # pack buffer and write back into file
  print OUTF pack ("H*", $hex) or die "Problem writing: $!\n";

  close(INF);
  close(OUTF);
  rename ($outfile, $infile) || die  "Can't rename patched .TDB file: $!";
  print STDERR ("done.\n");
}

patch_tdb "mapy/geocaches.TDB", "7322";
