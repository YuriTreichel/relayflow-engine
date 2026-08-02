<?php

namespace App\Services;

class VariableInterpolatorService
{
    /**
     * Substitui marcadores no formato {{ path.to.key }} pelos valores reais presentes no contexto.
     */
    public function interpolate(mixed $pattern, array $context): mixed
    {
        if (is_string($pattern)) {
            return preg_replace_callback('/\{\{\s*([\w\.\-]+)\s*\}\}/', function ($matches) use ($context) {
                $key = $matches[1];
                $value = data_get($context, $key);

                if (is_array($value) || is_object($value)) {
                    return json_encode($value);
                }

                return $value ?? '';
            }, $pattern);
        }

        if (is_array($pattern)) {
            $result = [];
            foreach ($pattern as $key => $value) {
                $result[$key] = $this->interpolate($value, $context);
            }
            return $result;
        }

        return $pattern;
    }
}