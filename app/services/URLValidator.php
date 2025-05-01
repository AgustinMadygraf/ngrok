<?php
/* Path: app/services/URLValidator.php */

/**
 * Clase URLValidator
 * Contiene métodos dedicados para validar URLs.
 */
class URLValidator {
    /**
     * Valida si una cadena de texto es una URL válida.
     *
     * @param string $url La URL a validar.
     * @return bool True si la URL es válida, False en caso contrario.
     */
    public function validate(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
