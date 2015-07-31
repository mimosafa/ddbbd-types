<?php
namespace DanaDonBoomBoomDoo\Types;

use DDBBD as D;
$nonce = D\Nonce::getInstance( 'ddbbd-type' );
?>

<form action="" method="post" id="ddbbd-type">
<?php $nonce->nonce_field(); ?>
<?php submit_button(); ?>
</form>
