<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time', 600);

class PuzzleMaker{
	
	protected $defaults=array(
		"tilesX" => 3,
		"tilesY" => 3,
		"tileDimension" => 166,
		"connectorDimension" => 0.28,
		"connectorMultiplier" => 0.7
	);

	protected $settings=array();

	protected $tileSettings=array();
	protected $tiles=array();

	protected $drawer=null;

	public function __construct($params){
		$this->setSettings($params);

		$this->generateTiles();
	}

	public function getTiles(){
		return json_encode($this->tiles);
	}

	protected function setSettings($params){
		$this->settings=$this->defaults;

		foreach($params as $paramKey => $paramValue){
			$this->settings[$paramKey] = $paramValue;
		}
	}

	protected function generateTiles(){
		for($y=0; $y < $this->settings["tilesY"]; $y++){
			for($x=0; $x < $this->settings["tilesX"]; $x++){
				$this->setTileSettings($x, $y);
			}		
		}

		for($y=0; $y < $this->settings["tilesY"]; $y++){
			for($x=0; $x < $this->settings["tilesX"]; $x++){
				$this->createTile($x, $y);
			}		
		}
	}

	protected function setTileSettings($x, $y){
		$top=rand(1,2);
		$bottom=rand(1,2);
		$left=rand(1,2);
		$right=rand(1,2);

		if(isset($this->tileSettings[($x-1).'_'.$y]) && $this->tileSettings[($x-1).'_'.$y]["right"] !== 0){
			$left=1+($this->tileSettings[($x-1).'_'.$y]["right"] % 2);
		}

		if(isset($this->tileSettings[$x.'_'.($y-1)]) && $this->tileSettings[$x.'_'.($y-1)]["bottom"] !== 0){
			$top=1+($this->tileSettings[$x.'_'.($y-1)]["bottom"] % 2);
		}
		
		if($y === 0){
			$top=0;
		}
		if($y === $this->settings["tilesY"]-1){
			$bottom=0;
		}
		
		if($x === 0){
			$left=0;
		}
		if($x === $this->settings["tilesX"]-1){
			$right=0;
		}
		
		$this->tileSettings[$x.'_'.$y]=array(
			"top" => $top,
			"bottom" => $bottom,
			"left" => $left,
			"right" => $right
		);
	}


	protected function createTile($x, $y){
		$settings=$this->tileSettings[$x.'_'.$y];
		$conWidth=$this->settings["connectorDimension"]*$this->settings["tileDimension"];

		$width=$tileWidth=$this->settings["tileDimension"];
		$height=$tileHeight=$this->settings["tileDimension"];

		$top=0;
		$left=0;


		if($settings["left"] === 1){
			$width+=$conWidth;
			$left+=$conWidth;
		}
		if($settings["right"] === 1){
			$width+=$conWidth;
		}

		if($settings["top"] === 1){
			$height+=$conWidth;
			$top+=$conWidth;
		}
		if($settings["bottom"] === 1){
			$height+=$conWidth;
		}

		$im=new Imagick();
		$im->newImage($width, $height, 'none');

		$this->drawer=new ImagickDraw();
		$this->drawer->setFillColor('Black');
		$this->drawer->setStrokeColor('none');

		
		$this->drawer->pathStart();
		$this->drawer->pathMoveToAbsolute($left,$top);
		

		$this->drawVerticalConnector(1,$settings["top"]);
		$this->drawHorizontalConnector(1,$settings["right"]);
		$this->drawVerticalConnector(0,$settings["bottom"]);
		$this->drawHorizontalConnector(0,$settings["left"]);

		$this->drawer->pathFinish();

		$im->setImageFormat("png");
		$im->drawImage($this->drawer);

		$this->tiles['tile'.$x.'_'.$y]=array(
			"x" => $x,
			"y" => $y,
			"width" => $width,
			"height" => $height,
			"image" => base64_encode($im->getImageBlob())
		);
		
		$im->destroy(); 
	}

	protected function drawVerticalConnector($position=1, $direction=1){
		$conWidth=$this->settings["connectorDimension"]*$this->settings["tileDimension"];
		$width=$this->settings["tileDimension"];

		if($direction===0){
			if($position === 1){
				$this->drawer->pathLineToRelative($width,0);	
			}else{
				$this->drawer->pathLineToRelative(-$width,0);
			}
		}else{
			if($position === 1){
				$multiplyer=1;
			}else{
				$multiplyer=-1;
			}

			$this->drawer->pathLineToRelative($width*$multiplyer/2-$conWidth*$multiplyer/2,0);
			
			if($direction === 1){
				$this->drawer->pathCurveToRelative(
					$conWidth*$multiplyer*-$this->settings["connectorMultiplier"],
					-$conWidth*$multiplyer,
					$conWidth*$multiplyer*(1+$this->settings["connectorMultiplier"]),
					-$conWidth*$multiplyer,
					$conWidth*$multiplyer,
					0
				);
			}else{
				$this->drawer->pathCurveToRelative(
					$conWidth*$multiplyer*-$this->settings["connectorMultiplier"],
					$conWidth*$multiplyer,
					$conWidth*$multiplyer*(1+$this->settings["connectorMultiplier"]),
					$conWidth*$multiplyer,
					$conWidth*$multiplyer,
					0
				);
			}

			$this->drawer->pathLineToRelative($width*$multiplyer/2-$conWidth*$multiplyer/2,0);
		}
	}

	protected function drawHorizontalConnector($position=1, $direction=1){
		$conWidth=$this->settings["connectorDimension"]*$this->settings["tileDimension"];
		$height=$this->settings["tileDimension"];

		if($direction===0){
			if($position === 1){
				$this->drawer->pathLineToRelative(0,$height);	
			}else{
				$this->drawer->pathLineToRelative(0,-$height);
			}
		}else{
			if($position === 1){
				$multiplyer=1;
			}else{
				$multiplyer=-1;
			}

			$this->drawer->pathLineToRelative(0,$height*$multiplyer/2-$conWidth*$multiplyer/2);
			
			if($direction === 1){
				$this->drawer->pathCurveToRelative(
					$conWidth*$multiplyer,
					$conWidth*$multiplyer*-$this->settings["connectorMultiplier"],
					$conWidth*$multiplyer,
					$conWidth*$multiplyer*(1+$this->settings["connectorMultiplier"]),
					0,
					$conWidth*$multiplyer
				);
			}else{
				$this->drawer->pathCurveToRelative(
					-$conWidth*$multiplyer,
					$conWidth*$multiplyer*-$this->settings["connectorMultiplier"],
					-$conWidth*$multiplyer,
					$conWidth*$multiplyer*(1+$this->settings["connectorMultiplier"]),
					0,
					$conWidth*$multiplyer
				);
			}

			$this->drawer->pathLineToRelative(0,$height*$multiplyer/2-$conWidth*$multiplyer/2);
		}
	}
}

$puzzle_maker=new PuzzleMaker(array(
	"tilesX" => $_POST["tilesX"],
	"tilesY" => $_POST["tilesY"]
));

echo $puzzle_maker->getTiles();