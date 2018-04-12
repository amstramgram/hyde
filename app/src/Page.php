<?php

namespace App;

use \Webuni\FrontMatter\FrontMatter;
use \cebe\markdown\GithubMarkdown;
use \Gumlet\ImageResize;


class Page {
	private $pages;
	private $files;
	private $route;
	private $path;
	private $name;
	private $baseName;
	private $document;
	private $title;
	private $datas;
	private $content;
	private $images = [];
	private $parsed;
	private $layout;
	private $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

	function __construct($c, $pages = []) {
		$this->route = $c->request->getUri()->getPath();
		$this->name = str_replace('/', '', $this->route);
		$this->pages = $pages;
		$this->parsed = $this->parse();
	}

	public function parse() {
		$this->files = glob(CONTENT_PATH .'/posts/2*-'.$this->name.'.md', GLOB_BRACE);
		if(!empty($this->files)){
			$this->path = reset($this->files);

			$document = $this->parseFrontMatter($this->path);

			$this->content = $this->parseMarkdown($document);
			$this->datas = $document->getData();
			$this->title = $this->datas['title'];
			$this->layout = $this->datas['layout'];
			$this->baseName = str_replace([POSTS_PATH, '/', '.md'], '', $this->path);

			$this->process();

			return true;
		}

		return false;
	}

	public function parseMarkdown($document){
		$parser = new GithubMarkdown();
		$content = $parser->parse($document->getContent());

		return $content;
	}

	public function parseFrontMatter($path){
		$frontMatter = new FrontMatter();

		$fileContent = file_get_contents($path);
		$document = $frontMatter->parse($fileContent);

		return $document;
	}

	public function process(){
		$images = glob(UPLOADS_PATH.'/'.$this->baseName.'/*.{jpg,png,gif}', GLOB_BRACE);

		foreach ($images as $image) {
			$image = [
				'url' => $this->parseUri($image),
				'sizes' => [
					'thumbnail' => $this->resize($image, 480, 480)
				],
				'name' => $this->parseName($image)
			];
			$this->images[] = json_decode(json_encode($image));
		}
	}

	public function resize($image, $w, $h) {
		$pathinfo = pathinfo($image);
		$thumbsPath = UPLOADS_PATH.'/'.$this->baseName.'/thumbs/';
		$newPathImage = $thumbsPath.$pathinfo['filename'].'-'.$w.'x'.$h.'.'.$pathinfo['extension'];

		if(!is_dir($thumbsPath)){
			mkdir($thumbsPath);
		}

		if(!file_exists($newPathImage)){
			$newimage = new ImageResize($image);
			$newimage->crop($w, $h, ImageResize::CROPCENTER)->save($newPathImage);
		}

		return $this->parseUri($newPathImage);
	}

	public function parseUri($path){
		return str_replace(PUBLIC_PATH, ROOT_URI, $path);
	}

	public function parseName($path){
		return str_replace('_', ' ', pathinfo($path)['filename']);
	}

	public function getPosts() {
		$posts = [];
		$files = $this->getFiles();
		foreach ($files as $file) {
			$pathinfo = pathinfo($file);
			$filename = $pathinfo['filename'];
			$route = preg_replace('~(\d{4})-(\d{2})-(\d{2})-~', '', $filename);
			$date = explode('-', $filename);

			$document = $this->parseFrontMatter($file);
			$data = $document->getData();

			$strdate = $date[0].'-'.$date[1].'-'.$date[2];

			$permalink = [
				'permalink' => ROOT_URI.'/'.$route,
				'title' => $data['title'],
				'slug' => $route,
				'date' => $strdate,
				'fullDate' => $date[2].' '.$this->months[ltrim($date[1], '0') - 1].' '.$date[0]
			];

			$posts[] = json_decode(json_encode($permalink));
		}

		return $posts;
	}

	public function getFiles() {
		return array_reverse(glob(CONTENT_PATH .'/posts/2*.md', GLOB_BRACE));
	}

	public function getFile() {
		if(empty($this->files)) {
			return false;
		}
		return $this->path;
	}

	public function getData($key = ""){
		if($key != ""){
			$result = (array_key_exists($key, $this->datas))? $this->datas[$key] : false ;
		}else{
			$result = $this->datas;
		}
		return $result;
	}

	public function getContent(){
		return $this->content;
	}

	public function getImages(){
		return $this->images;
	}

	public function getPath() {
		return $this->path;
	}

	public function getRoute() {
		return $this->route;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getLayout() {
		return $this->layout;
	}

	public function isParsed() {
		return $this->parsed;
	}

	public function isPage() {
		return (in_array($this->name, $this->pages))? true : false;
	}

	public function isNotFound(){
		if(!$this->getFile() && !$this->isPage()) {
			return true;
		}
		return false;
	}
}
