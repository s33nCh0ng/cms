<link rel="stylesheet" href="/admin/seo/webpage/seo-webpage-skybox.css">
<div style="width:900px">
<? 	if (is_numeric($page['website_page_id'])) {
		$page['website_page_ide'] = encrypt($page['website_page_id'],'website_page');
		
		//if ($uri) $aql = "website_uri_data { field, value where website_id = {$_POST['website_id']} and uri = '{$uri}' }";
		$aql="website_page_data { field, value where website_page_id = {$page['website_page_id']} }";
		$rs = aql::select($aql);
		if (is_array($rs))	
		foreach($rs as $r) {
			$fields[$r['field']]=$r['value'];
		}
	
		if (is_array($seo_field_array)) {
	?>
    		<fieldset style="width:872px; background:#f3f3f3; margin-bottom:20px; border: 1px solid #ccc; padding:10px;">
                <div id="opt_phrase">
                    <a id="opt_phrase_change" title="Change Opt Phrase" page_ide="<?=$page['website_page_ide']?>" field="opt_phrase" style="cursor:pointer;" ><?=aql::value('website_page.opt_phrase',$page['website_page_id'])?aql::value('website_page.opt_phrase',$page['website_page_id']):'Set Opt Phrase'?></a>
                </div>
            </fieldset>
			<fieldset style="width:872px; background:#f3f3f3; margin-bottom:20px; border: 1px solid #ccc; padding:10px;">
	<? /*
				$url = $_POST['url'];
				$post = array('_ajax'=>1);
				$json = GetCurlPage($url,$post);
				$seo_page = json_decode($json,true);
				if (is_array($seo_page) && is_array($page['vars'])) {
					foreach ( $page['vars'] as $var => $val ) {
						echo $var;
						if (!$val) echo ' (blank)';
						echo '<br />';
					}
				}
				else echo "Vars are not set";
		*/
	?>
					<div id="nickname"><a title="Change Nickname" page_ide="<?=$page['website_page_ide']?>" style="cursor:pointer; padding-bottom:10px;" id="nickname_change"><?=$page['nickname']?$page['nickname']:'Name This Page'?></a></div>
					
				</fieldset>
	<?
			// Insert the blank field records in the db for website_page fields that don't already exist
			$y=0;	
			foreach($seo_field_array as $type => $field_array) {
				
				foreach($field_array as $field => $max) {
					$rs2 = aql::select("website_page_data { value where field = '{$field}' and website_page_id = {$page['website_page_id']} }");
					if (!is_array($rs2)) {
						$data = array(
							'field'=>$field,
							'website_page_id'=>$page['website_page_id'],
							'mod__person_id'=>PERSON_ID
						);
						aql::insert('website_page_data',$data);
					}
				}
			
				
				if (!isset($header)) {
	?>
					<fieldset style="width:872px; background:#f3f3f3; margin-bottom:20px; border: 1px solid #ccc; padding:10px;">
						<legend style="border: 1px solid #ccc; background:#ffffff; font-weight:bold; padding:2px 5px 2px 5px;"><?=strtoupper(str_replace('_',' ',$type))?></legend>
	<?
					$header = $type;
				}
				else if ($header != $type) {
	?>
						</fieldset>
						<fieldset style="width:872px; background:#f3f3f3; margin-bottom:20px; border: 1px solid #ccc; padding:10px;">
							<legend style="border: 1px solid #ccc; background:#ffffff; font-weight:bold; padding:2px 5px 2px 5px;"><?=strtoupper(str_replace('_',' ',$type))?></legend>
	<?				
					$header = $type;	
					
				}
				$x = 0;
				
				foreach($field_array as $field => $char_max) {
					$x++;
					$y++;
					
	?>			
					<div style="float:left; padding:10px;">
						<label style="font-weight:bold; font-size:14px" for="<?=$field?>"><?=ucwords(str_replace(':',' ',str_replace('_',' ',$field)))?></label>
						<span style="font-size:10px;	color:#060;	margin-left:10px;" id="saved_<?=$y?>"></span><br>
	<?
						if ($field == 'h1_blurb' || $field == 'meta_description' || $field =='meta_keywords' )  {
							$width = 410;
	?>	
							<textarea style="width:410px; height:150px;" max="<?=$char_max?>" class="seo-input" wp_id="<?=$page['website_page_id']?>" saved_id="saved_<?=$y?>" field="<?=$field?>"><?=htmlspecialchars($fields[$field])?></textarea>
	<?
						} else {
							$width = 850;
	?>					
							<input type="text" class="seo-input" max="<?=$char_max?>" field="<?=$field?>" wp_id="<?=$page['website_page_id']?>" saved_id="saved_<?=$y?>" value="<?=htmlspecialchars($fields[$field])?>" style="width:850px;" />
	<?                    
						}
	?>					
    					<div id="<?=$field?>_counter" style="font-size:10px; text-align:right; width:<?=$width?>px">Characters <span id="<?=$field?>_char_count"></span> / <?=$char_max?></div>
					</div>
	<?				
					if ($x==3) {
						$x=0;
	?>
						<div class="clear"></div>
	<?
					}
	
				}
			}
		}
	?>
	</fieldset>
	<div style="clear:both;"></div>
	</div>
<? 
}
?>