<?php
class Peliculas {
    public $id;
    public $titulo;
    public $genero;
    public $fecha_lanzamiento;
    public $duracion;
    public $director;
    public $reparto;
    public $sinopsis;

    public function __construct($id, $titulo, $genero, $fecha_lanzamiento, $duracion, $director, $reparto, $sinopsis) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->genero = $genero;
        $this->fecha_lanzamiento = $fecha_lanzamiento;
        $this->duracion = $duracion;
        $this->director = $director;
        $this->reparto = $reparto;
        $this->sinopsis = $sinopsis;
    }

    public static function fromArray($data) {
        return new self(
            $data['id'] ?? null,
            $data['titulo'] ?? null,
            $data['genero'] ?? null,
            $data['fecha_lanzamiento'] ?? null,
            $data['duracion'] ?? null,
            $data['director'] ?? null,
            $data['reparto'] ?? null,
            $data['sinopsis'] ?? null
        );
    }

    public function toArray() {
        return get_object_vars($this);
    }
}
?>
