<?php

namespace Cmdobueno\Mod\Rules;

use Illuminate\Contracts\Validation\Rule;

class IntegerSize implements Rule
{
    public int $min = 0;
    public int $max = PHP_INT_MAX;
    
    public function __construct(int $min, int $max)
    {
        $this->min = $min;
        $this->max = $max;
    }
    
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $value >= $this->min && $value <= $this->max;
    }
    
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be between ' . $this->min . ' and ' . $this->max;
    }
}
