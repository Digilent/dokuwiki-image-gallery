<?php
/********************************************************************************************************************************
*
* Dokuwiki Image Gallery by Digilent
*
* Written By Sam Kristoff
*
* www.github.com/digilent/dokuwiki-image-gallery
* www.digilent.com
*
/*******************************************************************************************************************************/
  
 
// must be run within DokuWiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'syntax.php';
//Using PEAR Templates
require_once "HTML/Template/IT.php";

date_default_timezone_set('America/Los_Angeles');
 
/********************************************************************************************************************************
* All DokuWiki plugins to extend the parser/rendering mechanism
* need to inherit from this class
********************************************************************************************************************************/
class syntax_plugin_digilentimagegallery extends DokuWiki_Syntax_Plugin 
{
	//Return Plugin Info
	function getInfo() 
	{
        return array('author' => 'Sam Kristoff',
                     'email'  => 'admin@digilent.com',
                     'date'   => '2016-05-06',
                     'name'   => 'Digilent Image Gallery',
                     'desc'   => 'Dokuwiki Image Gallery by Digilent',
                     'url'    => 'www.github.com/digilent/dokuwiki-image-gallery');
    }
	
	//Store user variables to parse in one pass
	protected $images = array();
	 
    function getType() { return 'protected'; }
    function getSort() { return 32; }
  
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('{{Digilent Image Gallery.*?(?=.*?}})',$mode,'plugin_digilentimagegallery');
		
		//Add Internal Pattern Match For Product Page Elements	
		$this->Lexer->addPattern('\|.*?(?=.*?)\n','plugin_digilentimagegallery');
    }
	
    function postConnect() {
      $this->Lexer->addExitPattern('}}','plugin_digilentimagegallery');
    }
	 
    function handle($match, $state, $pos, &$handler) 
	{	
		
		switch ($state) 
		{		
			case DOKU_LEXER_ENTER :
				break;
			case DOKU_LEXER_MATCHED :					
				//Find The Token And Value (Before '=' remove white space, convert to lower case).
				$tokenDiv = strpos($match, '=');											//Find Token Value Divider ('=')
				$prettyToken = trim(substr($match, 1, ($tokenDiv - 1)));					//Everything Before '=', Remove White Space
				$token = strtolower($prettyToken);											//Convert To Lower Case
				$value = substr($match, ($tokenDiv + 1));									//Everything after '='
				switch($token)
				{
					case 'image':
						$imageUrl = ml(explode('?', $value)[0]);
						array_push($this->images, $imageUrl);
						break;					
					default:						
						break;
				}
				return array($state, $value);
				break;
			case DOKU_LEXER_UNMATCHED :
				break;
			case DOKU_LEXER_EXIT :
			
				//Set first image meta data
				/*
				global $ID;
				$metaData = p_get_metadata($ID);
				$metaData["relation"]["firstimage"] = 'ASDF';
				p_set_metadata($ID, $metaData);
				*/
								
				//----------Process User Data Into Image Gallery----------
				
				//Load HTML Template				
				$imageGalleryTpl = new HTML_Template_IT(dirname(__FILE__) . "/templates");
				$imageGalleryTpl->loadTemplatefile("image-gallery.tpl.html", true, true);
				
				//Add Large Image
				$imageGalleryTpl->setCurrentBlock("LARGEIMAGE");
				$imageGalleryTpl->setVariable("HREF", $this->images[0]);
				$imageGalleryTpl->setVariable("SRC", $this->images[0]);
				$imageGalleryTpl->parseCurrentBlock("LARGEIMAGE");
				
				//Add Thumbnails				
				$imageIndex = 0;
				foreach($this->images as $image)
				{
					$imageGalleryTpl->setCurrentBlock("THUMBNAILS");
					$imageGalleryTpl->setVariable("NUMBER", $imageIndex);
					$imageGalleryTpl->setVariable("SRC", $image);
					$imageGalleryTpl->parseCurrentBlock("THUMBNAILS");
					$imageIndex++;
				}
				
				logger("Processing Data");				
				$output = $imageGalleryTpl->get(); 
				
				//Clear Image Array
				$this->images = array();
				
				return array($state, $output);				
				break;
			case DOKU_LEXER_SPECIAL :
				break;
		}
		
		return array($state, $match);
    }
 
    function render($mode, &$renderer, $data) 
	{
    // $data is what the function handle return'ed.
        if($mode == 'xhtml')
		{
			switch ($data[0]) 
			{
			  case DOKU_LEXER_ENTER : 
				break;
			  case DOKU_LEXER_MATCHED :				
				break;
			  case DOKU_LEXER_UNMATCHED :
				break;
			  case DOKU_LEXER_EXIT :
			  
				//Extract cached render data and add to renderer
				$output = $data[1];				
				$renderer->doc .= $output;				
				break;
				
			  case DOKU_LEXER_SPECIAL :
				break;
			}			
            return true;
        }
        return false;
    }
	
	
	
}

function logger($value, $id="Unknown Page", $log="log")
{	
	$filePath = "/var/www/html/logs/" . $log . ".txt";
	file_put_contents($filePath, date("Y m d h:i:s A") . " - " . $id);
	file_put_contents($filePath, "\n" . $value . "\n", FILE_APPEND);
	return true;
}	