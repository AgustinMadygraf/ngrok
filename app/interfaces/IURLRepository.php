<?php
/*Path: app/Interfaces/IURLRepository.php*/

/**
 * Interface IURLRepository
 * Define las operaciones que cualquier repositorio de URLs debe implementar.
 */
interface IURLRepository {
    /**
     * Guarda una URL en el repositorio.
     *
     * @param string $url La URL a guardar.
     * @return bool True si se guardó correctamente, False en caso contrario.
     */
    public function saveURL(string $url): bool;

    /**
     * Recupera todas las URLs almacenadas.
     *
     * @return array Un arreglo de URLs.
     */
    public function getAllURLs(): array;

    /**
     * Recupera la última URL almacenada en el repositorio.
     *
     * @return string|null La última URL o null si no hay registros.
     */
    public function getLastURL(): ?string;
}
