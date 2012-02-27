<?php
  /* THESE SETTINGS ARE FREE TO EDIT */

  define("PIRATENPAD_MAIL",      "YOURADMINMAILADRESSFORTHEPAD");
  define("PIRATENPAD_PASS",      "YOURADMINPASSWORDFORTHEPAD");

  define("PIRATENPAD_SERVER",    "https://BLAFASEL.piratenpad.de");
  define("PIRATENPAD_PORT",      443);

  /* STOP EDITING HERE IF YOU DO NOT KNOW WHAT YOU ARE DOING */

  // PIRATENPAD_TEMP = ./temp/.
  define("PIRATENPAD_TEMPPART",  dirname(__FILE__) . "/temp");
  define("PIRATENPAD_TEMP",      PIRATENPAD_TEMPPART . "/.");

  define("PIRATENPAD_ACCOUNTS",  "/ep/admin/account-manager/");
  define("PIRATENPAD_LOGIN",     "/ep/account/sign-in");
  define("PIRATENPAD_LOGOUT",    "/ep/account/sign-out");
  define("PIRATENPAD_NEW",       "/ep/admin/account-manager/new");
  define("PIRATENPAD_TIMEOUT",   5);
  define("PIRATENPAD_USERAGENT", "Piratenpad-Bot");
  define("PIRATENPAD_PADLIST",   "/ep/padlist/all-pads");

  /* STOP EDITING HERE */

  function callPage($url, $post, $cookie) {
    $options = array(
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_USERAGENT      => PIRATENPAD_USERAGENT,
      CURLOPT_VERBOSE        => 1
    );

    if ($post != null) {
      $options[CURLOPT_POST]       = true;
      $options[CURLOPT_POSTFIELDS] = $post;
    }

    if ($cookie != null) {
      $options[CURLOPT_COOKIEFILE] = $cookie;
      $options[CURLOPT_COOKIEJAR]  = $cookie;
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, $options);
    $output = curl_exec($curl);
    curl_close($curl);  
    return $output;
  }
 
  function checkTempPath($mail) {
    return (dirname(PIRATENPAD_TEMP . $mail) === PIRATENPAD_TEMPPART);
  }

  function piratenpadGetPadlist($mail) {
    $test = "all pads";

    $output = callPage(PIRATENPAD_SERVER . PIRATENPAD_PADLIST, "",
                       PIRATENPAD_TEMP . $mail);

  if((strpos($output, PIRATENPAD_MAIL) !== false) &&
            (strpos($output, PIRATENPAD_LOGOUT) !== false) &&
            (strpos($output, $test) !== false)) {
	    return($output);
  } else {
	    return 0;
  }
  }

  function piratenpadAddAccount($mail, $name, $password) {
    $test = "Account " . $name . " (" . $mail . ") created successfully.";
 
    $output = callPage(PIRATENPAD_SERVER . PIRATENPAD_NEW,
                       "email=" . urlencode($mail) . "&fullName=" . urlencode($name) . "&tempPass=" . urlencode($password) . "&btn=Create%20Account",
                       PIRATENPAD_TEMP . $mail);

    return ((strpos($output, PIRATENPAD_MAIL) !== false) &&
            (strpos($output, PIRATENPAD_LOGOUT) !== false) &&
            (strpos($output, $test) !== false));
  }

  function piratenpadCheckAccount($mail) {
    $output = callPage(PIRATENPAD_SERVER . PIRATENPAD_ACCOUNTS,
                       null,
                       PIRATENPAD_TEMP . $mail);

    return ((strpos($output, PIRATENPAD_MAIL) !== false) &&
            (strpos($output, PIRATENPAD_LOGOUT) !== false) &&
            (strpos($output, $mail) === false));
  }

  function piratenpadFinit($mail) {
    $result = false;
 
    if (file_exists(PIRATENPAD_TEMP . $mail)) {
      unlink(PIRATENPAD_TEMP . $mail);

      $result = (!file_exists(PIRATENPAD_TEMP . $mail));
    }

    return $result;
  }

  function piratenpadInit($mail) {
    $result = false;

    if (!file_exists(PIRATENPAD_TEMP . $mail)) {
      callPage(PIRATENPAD_SERVER,
               null,
               PIRATENPAD_TEMP . $mail);

      $result = file_exists(PIRATENPAD_TEMP . $mail);
    }

    return $result;
  }

  function piratenpadLogin($mail) {
    $output = callPage(PIRATENPAD_SERVER . PIRATENPAD_LOGIN,
                       "email=" . urlencode(PIRATENPAD_MAIL) . "&password=" . urlencode(PIRATENPAD_PASS),
                       PIRATENPAD_TEMP . $mail);

    return ((strpos($output, PIRATENPAD_MAIL) !== false) &&
            (strpos($output, PIRATENPAD_LOGOUT) !== false));
  }

  function piratenpadLogout($mail) {
    $output = callPage(PIRATENPAD_SERVER . PIRATENPAD_LOGOUT,
                       null,
                       PIRATENPAD_TEMP . $mail);

    return ((strpos($output, PIRATENPAD_MAIL) !== false) &&
            (strpos($output, PIRATENPAD_LOGIN) !== false));
  }

/**
 * @author Jay Gilford
 */
 
/**
 * get_links()
 *
 * @param string $url
 * @return array
 */
function get_links($url) {
 
    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();
 
    // Load the url's contents into the DOM
    @$xml->loadHTML($url);
 
    // Empty array to hold all links to return
    $links = array();
 
    //Loop through each <a> tag in the dom and add it to the link array
/*    foreach($xml->getElementsByTagName('a') as $link) {
	if((strpos($link->getAttribute('href'), "/ep")===false) AND (strpos($link->getAttribute('href'), "http")===false)) {
		$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
	}
    }*/

	foreach($xml->getElementsByTagName('tr') as $key=>$tr) {
		foreach($tr->getElementsByTagName('a') as $link) {
			if((strpos($link->getAttribute('href'), "/ep")===false) AND (strpos($link->getAttribute('href'), "http")===false)) {
				$links[$key] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
			}
		}

		foreach($tr->getElementsByTagName('td') as $td) {
			if ($td->getAttribute("class") == "lastEditedDate") {
				if($td->nodeValue == "never") {
					$links[$key]['last_edit_hours'] =  0;
				} else {
					$date = explode(" ", $td->nodeValue);
					if($date[1] == "hours") {
						$links[$key]['last_edit_hours'] =  $date[0];
					} else {
						$links[$key]['last_edit_hours'] =  $date[0]*24;
					}
				}
			}

			foreach($td->getElementsByTagName('img') as $img) {
				if((strpos($img->getAttribute('src'), "padlock")!==false)) {
					$links[$key]['locked'] =  true;
				}
			}
		}
	}
	
	// Hole eine Liste von Spalten
	foreach ($links as $key => $row) {
		$last_edit_hours[$key]    = $row['last_edit_hours'];
	}
	
	// Die Daten mit 'Band' absteigend, die mit 'Auflage' aufsteigend sortieren.
	// Geben Sie $data als letzten Parameter an, um nach dem gemeinsamen
	// Schlüssel zu sortieren.
	array_multisort($last_edit_hours, SORT_ASC, $links);

	//Return the links
	return $links;
} 

header('Content-Type: text/html; charset=UTF-8');

  $address  = "clemens-john@gmx.de";
  $name     = "Clemens John";
  $password = "blafasel";

  $done = false;

  // check if temp file name is harmful
  if (checkTempPath($address)) {
    // initialize session
    if (piratenpadInit($address)) {
      // log in to piratenpad
      if (piratenpadLogin($address)) {
	$pads = piratenpadGetPadlist($address);
	$piratenpad = "http://oldenburg.piratenpad.de/";
//	echo "<h1>Pads im Piratenpad <a href=\"$piratenpad\">$piratenpad</a></h1>";
echo "<span style=\"	font: 8pt Arial, Helvetica, sans-serif;
	margin:0em;
	padding:0em;
	font-size:76%;\">";
echo "<ul>";
	foreach(get_links($pads) as $padlink) {
		$line = "<li><a href=\"$piratenpad$padlink[url]\" target=\"_blank\">$padlink[text] (";
		if ($padlink['last_edit_hours']==0) {
		      $line .= "niemals)";
		} else {
			$line .= "vor ";
			if($padlink['last_edit_hours']>24) {
				$line .= ($padlink['last_edit_hours']/24)." Tagen)";
			} else {
				$line .= $padlink['last_edit_hours']." Stunden)";
			}
		}
		if($padlink['locked']) {
			$line .= " (nicht öffentlich)";
		}
		$line .= "</a></li>";
		echo $line;
	}
echo "</ul>";
echo "</span>";
        // log out of piratenpad
        if (!piratenpadLogout($address)) {
          print("logout failed");
        }

      } else {
        print("login failed");
      }
    } else {
      print("init failed");
    }
  } else {
    print("harmful temp path");
  }

  // finalize session
  if (!piratenpadFinit($address)) {
    print("finit failed");
  }
 
  if ($done) {
    // show some message or so
  }
?>