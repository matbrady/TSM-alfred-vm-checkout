<?php  

include 'vm-checkout.php';

$vm = new VMS( "vm" );

echo $vm->fetchVmResults( "vm" );

?>