<?php

declare (strict_types=1);

function util_randstring (int $nbytes) :string {
   return bin2hex (random_bytes ($nbytes));
}

?>
