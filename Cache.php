<?php

class Cache{

	public $repertoire;
	public $duree;  // duree de vie du cache en minutes
	public $tab;

	public function __construct($repertoire,$duree){
		$this->repertoire=$repertoire;
		$this->duree=$duree;
	}

	public function write($fichier,$contenu){
		return file_put_contents($repertoire.'/'.$fichier, $contenu);
	}

	public function read($fichier){
		$file = $this->repertoire.'/'.$fichier;
		
		if (!file_exists($file)){
			return false;
		}

		$vie = (time() -filemtime($file)) / 60;
		if ($vie > $this->duree){
			return false;
		}

		return file_get_contents($file);

	}


	public function delete($fichier){
		$file = $this->repertoire.'/'.$fichier;
		if (file_exists($file)){
			unlink($fichier);
		}
	}

	public function clear(){
		$fichiers = glob($this->repertoire.'/*');
		foreach ($fichiers as $fichier) {
			unlink($fichier);
		}
	}

	public function inc($fichier,$cache = null){
		if (!$cache){
			$cache = basename($fichier);
		}
		
		if ($contenu = $this->read($cache)){
			echo $contenu;
			return true;
		}
		ob_start();
		require $fichier;
		$contenu = ob_get_clean();
		$this->write($cache, $contenu);
		echo $contenu;
		return true;
	}

	public function start($cache){
		if ($contenu = $this->read($cache)){ 
			echo $contenu;
			$this->tab = false;
			return true;
		}
		ob_start();
		$this->tab = $cache;
	}

	public function end(){
		if ($this->buffer){
			return false;
		}
		$contenu = ob_get_clean();
		$this->write($this->tab, $contenu);
	}

}