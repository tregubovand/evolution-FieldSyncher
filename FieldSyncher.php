<?php
	/**
	* Class made to synch values of the inputs by using simple patterns
	*/
	class FieldSyncher
	{
		public static $modx;
		public static $id;
		public static $outputValue;
		public static $tables = [];

		public static function init($modx, $pattern){
			self::$id = $modx->Event->params['id'];
			self::$modx = $modx;

			preg_match_all('/\[\*(.*?)\*\]/',$pattern,$tvList);

			$tvValues = self::getTVValues($tvList[1]);
			
			return self::getOutputString($tvValues, $pattern);
		}
		public static function saveTV($type, $field, $value){
			$id = self::$id;

			if($type == 'content'){
				$site_content = self::$modx->getFullTableName('site_content');
				self::$modx->db->query("UPDATE $site_content SET $field = '$value' WHERE id='$id'");
			}
			if($type == 'tv'){
				$tvId = getTVid($field);
				$site_tmplvar_contentvalues = self::$modx->getFullTableName('site_tmplvar_contentvalues');
				self::$modx->db->query("UPDATE $site_tmplvar_contentvalues SET value = '$value' WHERE contentid='$id' AND tmplvarid='$tvId'");
			}
		}
		private static function getOutputString($tvArray, $pattern){
			foreach ($tvArray as $tvName => $tvValue) {
				$pattern = str_replace("[*$tvName*]", $tvValue, $pattern);
			}
			self::$outputValue = $pattern;
			return $pattern;
		}
		private static function getTV(string $tv){
			$id = self::$id;
			
			$tvId = self::getTVid($tv);
			$site_tmplvar_contentvalues = self::$modx->getFullTableName('site_tmplvar_contentvalues');
			$tvValue = self::$modx->db->query("SELECT value FROM $site_tmplvar_contentvalues WHERE contentid='$id' AND tmplvarid='$tvId'");

			if(!$tvValue){
				return '';
			}

			return $tvValue->fetch_row()[0];
		}
		private static function getTVid($tvName){
			$site_tmplvars = self::$modx->getFullTableName('site_tmplvars');
			$tvId = self::$modx->db->query("SELECT id FROM $site_tmplvars WHERE name = '$tvName'");
			if(!$tvId){
			 	return '';
			}
			return $tvId->fetch_row()[0];
		}
		private static function getTVValues(array $tvList){
			foreach ($tvList as $tv) {
				yield $tv => self::getTV($tv);
			}
		}
	}
 ?>
