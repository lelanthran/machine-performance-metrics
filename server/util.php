<?php

declare (strict_types=1);

function util_randstring ($nbytes) {
   return bin2hex (random_bytes ($nbytes));
}
?>
