#!/usr/bin/php
<?php
/*********************************************************************************
 * FairnessTNA is a Workforce Management program forked from TimeTrex in 2013,
 * copyright Aydan Coskun. Original code base is copyright TimeTrex Software Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact Aydan Coskun via issue tracker on github.com/aydancoskun
 ********************************************************************************/
/*
 * File Contributed By: Open Source Consulting, S.A.   San Jose, Costa Rica.
 * http://osc.co.cr
 */

if ( PHP_SAPI != 'cli' ) {
	echo "This script can only be called from the Command Line.\n";
	exit;
}

// This script will send an email notification to the translator(s) listed in any
// messages.po file.  The notification basically says that the application has
// been updated and it would be great if they can review/update the translations.

// This script is intended to be run by hand.  It is important that one person be in
// charge of this, as we do not want to be spamming people over and over.

   $root_dir = '../../interface/locale';
   if( count( $argv ) > 1 ) {
      $root_dir = $argv[1];
   }

   $d = opendir( $root_dir );

   if( $d ) {

      $outpath = $root_dir . '/' . 'statistics.txt';
      $fh = fopen( $outpath, 'w' );

      $ignore_dirs = array( '.', '..', 'CVS' );
      while (false !== ($file = readdir($d))) {
         if( is_dir( $root_dir . '/' . $file ) && !in_array( $file, $ignore_dirs) ) {
            $stats = calcStats( $root_dir, $file );
            $pct = $stats['pct_complete'];
            $team = $stats['team'];
            $trans = $stats['translator'];

            $string_lang = lang($file);

            $emailTeam= search_email($team);
            if ($emailTeam != NULL){
            	send_mail($emailTeam,'FairnessTNA',$string_lang,$pct);
            }

            $emailTrans= search_email($trans);
            if ($emailTrans != NULL){
            	send_mail($emailTrans,'FairnessTNA',$string_lang,$pct);
            }
            fwrite( $fh, "$file|$pct|$team|$trans|$string_lang\n" );
         }
      }
      closedir( $d );

      fclose( $fh );
   }

   function calcStats( $root_dir, $locale ) {
      $messages = 0;
      $translations = 0;
      $fuzzy = 0;

     	$team = '';
     	$trans = '';

      $path = $root_dir . '/' . $locale . '/LC_MESSAGES/messages.po';
      // echo "<li><b>$path</b>";
      if( file_exists( $path ) ) {
         $lines = file( $path );

         $in_msgid = false;
         $in_msgstr = false;
         $found_translation = false;
         $found_msg = false;


         foreach( $lines as $line ) {
            // ignore comment lines
            if( $line[0] == '#' ) {
               continue;
            }

            // Parse out the contributors.
            if( strstr( $line, '"Language-Team: ' ) ) {
               $endpos = strpos( $line, '\n' );
               if( $endpos === false ) {
                  $endpos = strlen( $line ) - 2;
               }
               $len = strlen('"Language-Team: ');
               $field = substr( $line, $len, $endpos - $len );
               $names = explode( ',', $field );
               foreach( $names as $name ) {
                  if( $name != 'none' ) {
                     if( $team != '' ) {
                       $team .= ',';
                     }
                     $team .= trim( $name );
                  }
               }
            }

            //Parse the Last-Translator
            if( strstr( $line, '"Last-Translator: ' ) ) {
               $endpos = strpos( $line, '\n' );
               if( $endpos === false ) {
                  $endpos = strlen( $line ) - 2;
               }
               $len = strlen('"Last-Translator: ');
               $field = substr( $line, $len, $endpos - $len );
               $Transnames = explode( ',', $field );
               foreach( $Transnames as $Transname ) {
                  if( $Transname != 'Automatically generated' ) {
                     if( $trans != '' ) {
                        $trans .= ',';
                     }
                     $trans .= trim( $Transname );
                  }
               }
            }

            if( strstr( $line, 'msgid "' ) ) {
              $in_msgid = true;
               $in_msgstr = false;
               $found_msg = false;
               $found_translation = false;
            }
            if( $in_msgid && !$found_msg && strstr( $line, '"' ) && !strstr( $line, '""' ) ) {
              // echo "<li>msgid: $line";
               $found_msg = true;
               $messages ++;
            }
            else if( strstr($line, 'msgstr "') ) {
               $in_msgstr = true;
              $in_msgid = false;
            }
            if( $in_msgstr && $found_msg && !$found_translation ) {
               if( strstr( $line, '"' ) && !strstr( $line, '""' ) ) {
                  // echo "<li>msgstr: $line";
                  $translations ++;
                  $found_translation = true;
               }
            }
            else if( strstr( $line, '#, fuzzy' ) ) {
               $fuzzy ++;
            }
        }
      }
      $translations -= $fuzzy;
      $pct_complete = $messages ? (int)(($translations / $messages) * 100) : 0;

      return array( 'pct_complete' => $pct_complete, 'team' => $team, 'translator' => $trans );
   }

   function statistics($cad){
 		$names = explode(',', $cad);
		foreach ( $names as $name){
			if (strstr($name, ' translated')){
				$translated= $name;
			}else{
				if (strstr($name, ' fuzzy')){
					$fuzzy= $name;
				}else{
					if (strstr($name, ' untranslated')){
						$untranslated = $name;
					}
				}
			}
		}

		return array( 'translated' => $translated, 'fuzzy' => $fuzzy, 'untranslated' => $untranslated );
	}

function send_mail($to,$from,$lang,$pct){
$subject= <<<END
FairnessTNA updated, {$lang} translation out of date
END;

$msg= <<<END
to: {$to}
from: {$from}
subject: {$subject}

Hello,

You are receiving this email because you previously contributed {$lang} 
translations to FairnessTNA.  The application has recently been updated, which
means that new english text needs to be translated.

As of this moment, the {$lang} translation file is {$pct}% complete.

If you could find a few minutes to update the translation file, it would be
greatly appreciated.  In any event, we thank-you for your previous contribution,
which has helped the site reach many {$lang} readers.

You can always find the latest language files and instructions at:

*TODO_NEED_URL_HERE*

Best,

FairnessTNA
END;

	$msg = wordwrap($msg, 100);
	$subject = wordwrap($subject, 100);
	mail($to, $subject, $msg);

}

	function lang($cad){
		$languages = array('af_ZA' => 'Afrikaans', 'am_ET' => 'Amharic', 'as_IN' => 'Assamese', 'az_AZ' => 'Azerbaijani',
						 	 'be_BY' => 'Belarusian', 'bg_BG' => 'Bulgarian', 'bn_IN' => 'Bengali', 'bo_CN' => 'Tibetan',
						 	 'br_FR' => 'Breton', 'bs_BA' => 'Bosnian', 'ca_ES' => 'Catalan', 'ce_RU' => 'Chechen', 'co_FR' => 'Corsican',
						    'cs_CZ' => 'Czech', 'cy_GB' => 'Welsh', 'da_DK' => 'Danish', 'de_DE' => 'German', 'dz_BT' => 'Dzongkha', 'el_GR' => 'Greek',
						    'en_US' => 'English', 'es_ES' => 'Spanish', 'et_EE' => 'Estonian', 'fa_IR' => 'Persian', 'fi_FI' => 'Finnish',
						    'fj_FJ' => 'Fijian', 'fo_FO' => 'Faroese', 'fr_FR' => 'French', 'ga_IE' => 'Irish', 'gd_GB' => 'Scots',
						    'gu_IN' => 'Gujarati', 'he_IL' => 'Hebrew', 'hi_IN' => 'Hindi', 'hr_HR' => 'Croatian', 'hu_HU' => 'Hungarian',
 							 'hy_AM' => 'Armenian', 'id_ID' => 'Indonesian', 'is_IS' => 'Icelandic', 'it_IT' => 'Italian', 'ja_JP' => 'Japanese',
 							 'jv_ID' => 'jv', 'ka_GE' => 'GeorgKoreanian', 'kk_KZ' => 'Kazakh', 'kl_GL' => 'Kalaallisut', 'km_KH' => 'Khmer',
 							 'kn_IN' => 'Kannada', 'kok_IN' => 'Konkani', 'ko_KR' => 'Korean', 'lo_LA' => 'Laotian', 'lt_LT' => 'Lithuanian',
 							 'lv_LV' => 'Latvian', 'mg_MG' => 'Malagasy', 'mk_MK' => 'Macedonian', 'ml_IN' => 'Malayalam', 'mni_IN' => 'Manipuri',
 							 'mn_MN' => 'Mongolian', 'mr_IN' => 'Marathi', 'ms_MY' => 'Malay', 'mt_MT' => 'Maltese', 'my_MM' => 'Burmese',
 							 'na_NR' => 'Nauru', 'nb_NO' => 'Norwegian', 'ne_NP' => 'Nepali', 'nl_NL' => 'Dutch', 'nn_NO' => 'Norwegian',
 							 'oc_FR' => 'Occitan', 'or_IN' => 'Oriya', 'pa_IN' => 'Punjabi', 'pl_PL' => 'Polish', 'ps_AF' => 'Pashto', 'pt_PT' => 'Portuguese',
 							 'rm_CH' => 'Rhaeto-Roman', 'rn_BI' => 'Kirundi', 'ro_RO' => 'Romanian', 'ru_RU' => 'Russian', 'sa_IN' => 'Sanskrit',
 							 'sc_IT' => 'Sardinian', 'sg_CF' => 'Sango', 'si_LK' => 'Sinhalese', 'sk_SK' => 'Slovak', 'sl_SI' => 'Slovenian',
 							 'so_SO' => 'Somali', 'sq_AL' => 'Albanian', 'sr_YU' => 'Serbian', 'sv_SE' => 'Swedish', 'te_IN' => 'Telugu',
 							 'tg_TJ' => 'Tajik', 'th_TH' => 'Thai', 'tk_TM' => 'Turkmen', 'tl_PH' => 'Tagalog', 'to_TO' => 'Tonga', 'tr_TR' => 'Turkish',
 							 'uk_UA' => 'Ukrainian', 'ur_PK' => 'Urdu', 'uz_UZ' => 'Uzbek', 'vi_VN' => 'Vietnamese', 'wa_BE' => 'wa', 'wen_DE' => 'Sorbian');

		return ($languages[$cad]);
	}

	function search_email($cad){
			$names = '';
			$i = 0;
			$name = '';

			$names = explode( ' ', $cad );
			foreach( $names as $name ) {
				for ($i = 0; $i <= strlen($name); $i++){
					if ($name[$i] == "<" || $name[$i] == ">"){
						$name[$i] = " ";
					}
				}
				if (check_email_address(trim($name))){
					return($name);
					break;
				}
         }
		}

   function check_email_address($email) {
			// check @
			if (!ereg("[^@]{1,64}@[^@]{1,255}", $email)) {
				// Email error @'s
				return false;
			}

			$email_array = explode("@", $email);
			$local_array = explode(".", $email_array[0]);
			for ($i = 0; $i < sizeof($local_array); $i++) {
				if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^
							_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
					return false;
				}
			}

			if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
				// check domain
				$domain_array = explode(".", $email_array[1]);

				if (sizeof($domain_array) < 2) {
					return false; // Domain false
				}
				for ($i = 0; $i < sizeof($domain_array); $i++) {
					if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
						return false;
					}
				}
			}
			return true;
		}

?>
