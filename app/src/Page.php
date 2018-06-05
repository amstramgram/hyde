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
		$this->parsed = $this->parsePage();
	}

	public function parsePage() {
		$this->files = glob(CONTENT_PATH .'/posts/2*-'.$this->name.'.md', GLOB_BRACE);
		if(!empty($this->files)){
			$this->path = reset($this->files);

			$document = $this->parseFrontMatter($this->path);

			$this->datas = $document->getData();
			$this->title = $this->datas['title'];
			$this->layout = $this->datas['layout'];
			$this->baseName = str_replace([POSTS_PATH, '/', '.md'], '', $this->path);
			$this->process();

			$this->content = $this->parseMarkdown($document);

			return true;
		}

		return false;
	}

	public function parseMarkdown($document){
		$parsed_doc = $document->getContent();

		preg_match_all('/!\[.*\]\(.*\)/', $document->getContent(), $matches_i);
		preg_match_all('/!g\(.*\)/', $document->getContent(), $matches_g);

		if( !empty($matches_i[0]) ){
			foreach($matches_i[0] as $match){
				$explode = explode('(', $match);
				$explode = explode(' ', $explode[1]);
				$name = str_replace(')','', $explode[0]);

				$key = array_search($name, array_column($this->images, 'name'));

				if( $key !== false ){
					$url = $this->images[$key]->sizes->large;
					$new = str_replace($name, $url, $match);

					$parsed_doc = str_replace($match, $new, $parsed_doc);
				}
			}
		}

		if( !empty($matches_g[0]) ){
			foreach($matches_g[0] as $index => $match){

				$explode = explode('(', $match);
				$explode = explode(' "', $explode[1]);

				$list_name = str_replace(')','', $explode[0]);
				$list_title = str_replace('")','', $explode[1]);
				$names = explode('|', $list_name);
				$titles = explode('|', $list_title);

				foreach($names as $i => $name){
					$key = array_search($name, array_column($this->images, 'name'));

					if( $key !== false ){
						$url = $this->images[$key]->sizes->large;
						$this->gallery[$index][] = ["src" => $url, "title" => $titles[$i]];
					}
				}

				$parsed_doc = str_replace("(".$list_name." \"".$list_title."\")", "", $parsed_doc);
			}
		}

		$parser = new GithubMarkdown();
		$content = $parser->parse($parsed_doc);

		preg_match_all('/<p>!g<\/p>/', $content, $matches_in, PREG_OFFSET_CAPTURE);


		if( !empty($matches_in[0]) ){
			$added_length = 0;
			foreach($matches_in[0] as $index => $match){
				$gallery = $this->parseGallery($index);
				$position = $match[1];
				$length = strlen($match[0]);
				$total_position = $position + $added_length;
				$added_length = strlen($gallery) - $length;
				$content = substr_replace($content, $gallery, $total_position, $length);
			}

		}

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
					'thumbnail' => $this->resize($image, 480, 480),
					'large' => $this->resize($image, 1200, 9999, "large")
				],
				'name' => $this->parseName($image)
			];
			$this->images[] = json_decode(json_encode($image));
		}
	}

	public function resize($image, $w = null, $h = null, $folder = "thumbs") {
		$pathinfo = pathinfo($image);
		$thumbsPath = UPLOADS_PATH.'/'.$this->baseName.'/'.$folder.'/';

		if($w === 9999 || $h === 9999){
			$size = getimagesize($image);

			if($w === 9999){
				$w = intval( ($h * $size[0]) / $size[1] );
			}
			if($h === 9999){
				$h = intval( ($size[1] * $w) / $size[0] );
			}
		}

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
		return str_replace('_', ' ', pathinfo($path)['filename'].".".pathinfo($path)['extension']);
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

	private function parseGallery($index){
		$html = '<div class="gallery">';
			$html .= '<div class="slides-wrapper">';
				$html .= '<div class="slides">';
					foreach($this->gallery[$index] as $item){
						$html .= '<figure>';
							$html .= '<img src="'.$item['src'].'" title="'.$item['title'].'">';
						$html .= '</figure>';
					}
				$html .= '</div>';
			$html .= '</div>';
		$html .= '</div>';

		return $html;
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
