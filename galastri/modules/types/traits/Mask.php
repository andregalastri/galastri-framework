<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to masking.
 */
trait Mask
{    
    /**
     * This method apply the given mask to a value and return the result. It removes every
     * non-alphanumeric chars from the string and replace any # or 0 from the mask into the each
     * char of the value. The number of replaces is equal to the value length and any other # char
     * left is removed. The 0 char, however, will remain.
     *
     * @param  mixed $mask                          The mask string with common chars that will be
     *                                              part of the mask and flag chars # and/or 0 that
     *                                              will replaced by the chars of the value. The #
     *                                              chars will be removed if not all of them is
     *                                              used. The 0 chars will remain.
     *
     * @param  mixed $cleanEdges                    When true, removes chars that left at the sides
     *                                              of unused flags.
     * 
     * @return self
     */
    public function mask(string $mask, bool $cleanEdges = false): self
    {
        $number = preg_replace('/[^\p{N}\p{L}]/', '', $this->getValue());
        $mask = preg_replace('/#|0/', '%s', $mask, strlen($number));

        $result = vsprintf($mask, str_split($number));
        $result = $cleanEdges ? preg_replace('/[^#0]#|#[^#0]/', '', $result) : $result;
        $result = preg_replace('/#/', '', $result);

        $this->execHandleValue($result);
        
        return $this;
    }
}
