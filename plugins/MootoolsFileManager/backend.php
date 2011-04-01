<?php 
/**
* Unified backend for ImageManager 
*
* Image Manager was originally developed by:
*   Xiang Wei Zhuo, email: xiangweizhuo(at)hotmail.com Wei Shou.
*
* Unified backend sponsored by DTLink Software, http://www.dtlink.com
* Implementation by Yermo Lamers, http://www.formvista.com
*
* (c) DTLink, LLC 2005.
* Distributed under the same terms as HTMLArea itself.
* This notice MUST stay intact for use (see license.txt).
*
* DESCRIPTION:
*
* Instead of using separate URL's for each function, ImageManager now
* routes all requests to the server through this single, replaceable,
* entry point. backend.php expects at least two URL variable parameters: 
*
* __plugin=ImageManager   for future expansion; identify the plugin being requested.
* __function=thumbs|images|editorFrame|editor|manager  function being called.
*
* Having a single entry point that strictly adheres to a defined interface will 
* make the backend code much easier to maintain and expand. It will make it easier
* on integrators, not to mention it'll make it easier to have separate 
* implementations of the backend in different languages (Perl, Python, ASP, etc.) 
*
* @see config.inc.php
*/

// Strip slashes if MQGPC is on
if(function_exists('set_magic_quotes_runtime')) @set_magic_quotes_runtime(0);
if(function_exists('get_magic_quotes_gpc') && @get_magic_quotes_gpc())
{
  $to_clean = array(&$_GET, &$_POST, &$_REQUEST, &$_COOKIE);
  while(count($to_clean))
  {
    $cleaning =& $to_clean[array_pop($junk = array_keys($to_clean))];
    unset($to_clean[array_pop($junk = array_keys($to_clean))]);
    foreach(array_keys($cleaning) as $k)
    {
      if(is_array($cleaning[$k]))
      {
        $to_clean[] =& $cleaning[$k];
      }
      else
      {
        $cleaning[$k] = stripslashes($cleaning[$k]);
      }
    }
  }
}

function size_to_bytes($s)
{
  if(preg_match('/([0-9\.])+([a-zA-Z]+)/', $s, $M))
  {
    switch(strtolower($M[2]))
    {      
      case 'm':
        return floor(floatval($M[1]) * 1024 * 1024);
        
      case 'b':
        return intval($M[1]);
        
      case 'kb':
        return floor(floatval($M[1]) * 1024);      
    }
  }
  
  if(floatval($s) < 10)   return floor(floatval($s) * 1024 * 1024); 
  if(floatval($s) < 1024) return floor(floatval($s) * 1024); // Kilobytes
  return intval($s); // Bytes
}

require_once('config.php');

// Ensure thumbnail path is OK
if(!isset($IMConfig['files_dir']) || !isset($IMConfig['images_dir']))
{
  unset($IMConfig['thumbs_dir']);
  unset($IMConfig['thumbs_url']);
}
else
{ 
  if(!file_exists($IMConfig['thumbs_dir'])) @mkdir($IMConfig['thumbs_dir']);
}

switch ( @$_REQUEST[ "__function" ] )
{
  case 'read-config':        
    // This is used so that the javascript can read the config 
    // so we don't have to have a js config and a php config duplicating
    // settings
    echo xinha_to_js($IMConfig);
    break;

  case 'image-manager':
    // include('mootools-filemanager/Assets/Connector/FileManager.php');
    include('XinhaFileManager.php');
    
    $browser = new XinhaFileManager(array(
      'images_dir'    => $IMConfig['images_dir'],
      'images_url'    => $IMConfig['images_url'],
      
      'thumbs_dir'    => $IMConfig['thumbs_dir'],
      'thumbs_url'    => $IMConfig['thumbs_url'],
      
      'assetBasePath' => $IMConfig['base_url'] .'/mootools-filemanager/Assets',
      
      'upload'        => $IMConfig['allow_images_upload'],
      'destroy'       => $IMConfig['allow_images_delete'],      
      'create'        => $IMConfig['allow_images_create_dir'],
      'move'          => $IMConfig['allow_images_move'],
      'download'      => $IMConfig['allow_images_download'],
      
      
      'maxUploadSize' => size_to_bytes($IMConfig['max_images_upload_size']),      
      'suggestedMaxImageDimension' => $IMConfig['suggested_images_image_dimension'],
                  
      'filter'        => 'image/',            
    ));

    $browser->fireEvent(!empty($_REQUEST['event']) ? $_REQUEST['event'] : null);
    break;
  
  case 'file-manager':
    include('mootools-filemanager/Assets/Connector/FileManager.php');

    $browser = new FileManager(array(
      'files_dir'     => $IMConfig['files_dir'],
      'files_url'     => $IMConfig['files_url'],
      
      'thumbs_dir'    => $IMConfig['thumbs_dir'],
      'thumbs_url'    => $IMConfig['thumbs_dir'],
      
      'assetBasePath' => $IMConfig['base_url'] .'/mootools-filemanager/Assets',
      
      'upload'        => $IMConfig['allow_files_upload'],
      'destroy'       => $IMConfig['allow_files_delete'],
      'create'        => $IMConfig['allow_files_create_dir'],
      'move'          => $IMConfig['allow_files_move'],
      'download'      => $IMConfig['allow_files_download'],
      
      
      'maxUploadSize' => size_to_bytes($IMConfig['max_files_upload_size']),      
      'suggestedMaxImageDimension' => $IMConfig['suggested_files_image_dimension'],
            
     // 'filter'        => $IMConfig['files_filter'],
            
    ));

    $browser->fireEvent(!empty($_REQUEST['event']) ? $_REQUEST['event'] : null);
    break;
}

?>
