<?php

class item {
	private $id;
	private $media_id;
	private $service_type;
	private $permalink;
	private $title;
	private $author;
	private $description;
	private $category;
	private $post_date;
	private $thumbnail;
	private $tag;

	public function get_media_id() { return $this->media_id; }
	public function get_service_type() { return $this->service_type; }
	public function get_permalink() { return $this->permalink; }
	public function get_title() { return $this->title; }
	public function get_author() { return $this->author; }
	public function get_description() { return $this->description; }
	public function get_category() { return $this->category; }
	public function get_post_date() { return $this->post_date; }
	public function get_thumbnail() { return $this->thumbnail; }
	public function get_tag() { return $this->tag; }
	public function set_media_id($x) { $this->media_id = $x; }
	public function set_service_type($x) { $this->service_type = $x; }
	public function set_permalink($x) { $this->permalink = $x; }
	public function set_title($x) { $this->title = $x; }
	public function set_author($x) { $this->author = $x; }
	public function set_description($x) { $this->description = $x; }
	public function set_category($x) { $this->category = $x; }
	public function set_post_date($x) { $this->post_date = $x; }
	public function set_thumbnail($x) { $this->thumbnail = $x; }
	public function set_tag($x) { $this->tag = $x; }

	public function __construct($id, $media_id, $service_type, $permalink, $title, $author, $description, $category, $post_date, $thumbnail, $tag)
	{
		$this->id = $id;
		$this->media_id = $media_id;
		$this->service_type = $service_type;
		$this->permalink = $permalink;
		$this->title = $title;
		$this->author = $author;
		$this->description = $description;
		$this->category = $category;
		$this->post_date = $post_date;
		$this->thumbnail = $thumbnail;
		$this->tag = $tag;
	}
}

?>