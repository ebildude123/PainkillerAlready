<?php

require_once("vendor/autoload.php");
require_once("vendor/bcosca/fatfree/lib/base.php");

F3::set("DB", new DB\SQL("mysql:host=" . apache_getenv("DB_HOST") . ";dbname=" . apache_getenv("DB_NAME") . ";charset=utf8", apache_getenv("DB_USER"), apache_getenv("DB_PASS")));

F3::set("Core", new \Tripod\Podcast(F3::get("DB")));
F3::set("Utilities", new \Tripod\Utilities());
F3::set("DEBUG", 3);

F3::get("Core")->setName("Painkiller Already");
F3::get("Core")->setDescription("Three gamers discuss games, current events, and tell a few stories.");
F3::get("Core")->setPrefix("PKA");
F3::set("feedburner", "Painkiller_Already");

F3::set("base_domain", F3::get("Utilities")->getBaseDomain());
F3::set("commit_count", F3::get("Utilities")->getCommitCount());
F3::set("description", F3::get("Core")->getDescription());
F3::set("domain", F3::get("Utilities")->getDomain());
F3::set("episodes", F3::get("Core")->getEpisodes());
F3::set("people", F3::get("Core")->getPeople());

F3::set("gplus", "107397414095793132493");
F3::set("twitter", "PKA_Archive");
F3::set("creator", "nehalvpatel");

F3::set("ONERROR",
	function ($f3) {
		F3::set("type", "error");
		F3::set("canonical", F3::get("domain") . "error");
		F3::set("title", F3::get("ERROR.code") . " &middot; " . F3::get("Core")->getName());
		
		$template = new Template;
		echo $template->render("views/base.tpl");
	}
, 60);

F3::route("GET /",
    function($f3) {
        F3::set("type", "episode");
        F3::set("current_episode", F3::get("episodes")[count(F3::get("episodes")) - 1]);
        F3::set("canonical", F3::get("domain") . "episode/" . F3::get("current_episode")->getNumber());
        F3::set("title", F3::get("Core")->getName());
        F3::set("source", "latest");
        
        if (F3::get("current_episode")->getTimelined() === true) {
            F3::set("timeline_author", F3::get("current_episode")->getTimelineAuthor());
        }
        
        $template = new Template;
        echo $template->render("views/base.tpl");
    }
, 60);

F3::route("GET /episode/@number",
    function($f3, $params) {
        F3::set("type", "episode");
        
        if ($params["number"] == "random") {
            F3::set("current_episode", F3::get("episodes")[array_rand(F3::get("episodes"))]);
            F3::reroute("/episode/" . F3::get("current_episode")->getNumber());
        } else {
            if (!is_numeric($params["number"])) {
                $f3->error(404);
            } else {
                foreach (F3::get("episodes") as $episode) {
                    if ($params["number"] == $episode->getNumber()) {
                        F3::set("current_episode", $episode);
                    }
                }
            }
        }
		
		if (!F3::exists("current_episode")) {
			$f3->error(404);
		}
        
        $guests = F3::get("current_episode")->getGuests();
        if (count($guests) == 0) {
            F3::set("guests_list", "Nobody");
        } else {
            if (count($guests) > 2) {
                $guests[count($guests) - 1] = "and " . strval($guests[count($guests) - 1]);
                F3::set("guests_list", join(", ", array_map("strval", $guests)));
            } else {
                F3::set("guests_list", join(" and ", array_map("strval", $guests)));
            }
        }
        
        if (F3::get("current_episode")->getTimelined() === true) {
            F3::set("timeline_author", F3::get("current_episode")->getTimelineAuthor());
        }
        
        F3::set("description", "Guests: " . F3::get("guests_list"));
        F3::set("canonical", F3::get("domain") . "episode/" . F3::get("current_episode")->getNumber());
        F3::set("title", "Episode #" . F3::get("current_episode")->getNumber() . " &middot; " . F3::get("Core")->getName());
        F3::set("source", "get");
        
        $template = new Template;
        echo $template->render("views/base.tpl");
    }
, 60);

F3::route("GET /person/@number",
    function($f3, $params) {
        F3::set("type", "person");
        
        if ($params["number"] == "random") {
            F3::set("current_person", F3::get("people")[array_rand(F3::get("people"))]);
            F3::reroute("/person/" . F3::get("current_person")->getID());
        } else {
            if (!is_numeric($params["number"])) {
                $f3->error(404);
            } else {
                foreach (F3::get("people") as $person) {
                    if ($params["number"] == $person->getID()) {
                        F3::set("current_person", $person);
                    }
                }
            }
        }
        
		if (!F3::exists("current_person")) {
			$f3->error(404);
		}
		
        $host_count = 0;
        $guest_count = 0;
        $sponsor_count = 0;
        foreach (F3::get("episodes") as $episode) {
            foreach ($episode->getHosts() as $host) {
                if ($host->getID() == F3::get("current_person")->getID()) {
                    $episode->setHighlighted(true);
                    $host_count++;
                }
            }
            
            foreach ($episode->getGuests() as $guest) {
                if ($guest->getID() == F3::get("current_person")->getID()) {
                    $episode->setHighlighted(true);
                    $guest_count++;
                }
            }
            
            foreach ($episode->getSponsors() as $sponsor) {
                if ($sponsor->getID() == F3::get("current_person")->getID()) {
                    $episode->setHighlighted(true);
                    $sponsor_count++;
                }
            }
        }
        
        F3::set("host_count", $host_count);
        F3::set("guest_count", $guest_count);
        F3::set("sponsor_count", $sponsor_count);
        F3::set("recent_videos", F3::get("current_person")->getRecentYouTubeVideos());
        F3::set("social_links", F3::get("current_person")->getSocialLinks());
        
        F3::set("description", F3::get("current_person")->getOverview());
        F3::set("canonical", F3::get("domain") . "episode/" . F3::get("current_person")->getID());
        F3::set("title", F3::get("current_person")->getName() . " &middot; " . F3::get("Core")->getName());
        
        $template = new Template;
        echo $template->render("views/base.tpl");
    }
, 60);

F3::route("GET /content",
    function($f3) {
        if (isset($_GET["id"])) {
            $id = trim($_GET["id"]);

            if (!empty($id)) {
                $id = urldecode($id);

                if (strpos($id, F3::get("domain")) !== FALSE) {
                    $id = str_replace(F3::get("domain") . "episode/", "", $id);
                    
                    if (strpos(F3::get("Core")->getPrefix(), $id) === FALSE) {
                        if (is_numeric($id)) {
                            $id = F3::get("Core")->getPrefix() . "_" . F3::get("Utilities")->padEpisodeNumber($id);
                        }
                    } else {
                        $id = F3::get("Core")->getPrefix() . "_" . F3::get("Utilities")->padEpisodeNumber($id);
                    }
                }
                
                $cache = true;
                if ($id == "random") {
                    $episode = F3::get("episodes")[array_rand(F3::get("episodes"))];
                    $cache = false;
                } else {
                    $episode = new \Tripod\Episode($id, F3::get("DB"));
                }
                
                $episode_data = array();

                $episode_data["Identifier"] = $episode->getIdentifier();
                $episode_data["Number"] = $episode->getNumber();
                $episode_data["DateTime"] = $episode->getDate();
                $episode_data["Date"] = date("F d, Y", strtotime($episode->getDate()));
                $episode_data["Reddit"] = $episode->getReddit();
                $episode_data["YouTube"] = $episode->getYouTube();
                $episode_data["YouTubeLength"] = $episode->getYouTubeLength();
                $episode_data["Cache"] = $cache;
                $episode_data["Link"] = F3::get("domain") . "episode/" . $episode->getNumber();
                
                foreach ($episode->getHosts() as $host) {
                    $host_data = array();
                    $host_data["ID"] = $host->getID();
                    $host_data["Name"] = $host->getName();
                    $host_data["URL"] = $host->getURL();
                    
                    $episode_data["People"]["Hosts"][] = $host_data;
                }
                
                foreach ($episode->getGuests() as $guest) {
                    $guest_data = array();
                    $guest_data["ID"] = $guest->getID();
                    $guest_data["Name"] = $guest->getName();
                    $guest_data["URL"] = $guest->getURL();
                    
                    $episode_data["People"]["Guests"][] = $guest_data;
                }
                
                foreach ($episode->getSponsors() as $sponsor) {
                    $sponsor_data = array();
                    $sponsor_data["ID"] = $sponsor->getID();
                    $sponsor_data["Name"] = $sponsor->getName();
                    $sponsor_data["URL"] = $sponsor->getURL();
                    
                    $episode_data["People"]["Sponsors"][] = $sponsor_data;
                }

                $episode_data["Timeline"] = array();
                if ($episode->getTimelined()) {
                    $author = $episode->getTimelineAuthor();
                    $episode_data["Timeline"]["Author"]["Name"] = $author->getDisplayName();
                    $episode_data["Timeline"]["Author"]["Link"] = $author->getDisplayLink();
                    
                    $episode_data["Timeline"]["Timestamps"] = array();
                    foreach ($episode->getTimestamps() as $timestamp) {
                        $timestamp_data = array();
                        $timestamp_data["HMS"] = $timestamp->getTime();
                        $timestamp_data["Value"] = $timestamp->getValue();
                        $timestamp_data["URL"] = $timestamp->getURL();
                        $timestamp_data["Begin"] = $timestamp->getBegin();
                        $timestamp_data["End"] = $timestamp->getEnd();
                        $timestamp_data["Width"] = $timestamp->getWidth();
                        
                        $episode_data["Timeline"]["Timestamps"][] = $timestamp_data;
                    }
                }

                echo json_encode($episode_data);
            }
        }
    }
, 60);

F3::route("GET /search",
    function($f3) {
        if (!isset($_GET["query"])) {
            echo json_encode(F3::get("Core")->getSearchResults(""));
        } else {
            echo json_encode(F3::get("Core")->getSearchResults($_GET["query"]));
        }
    }
, 60);

F3::route("GET /credits",
    function($f3) {
        F3::set("type", "credits");
        F3::set("canonical", F3::get("domain") . "credits");
        F3::set("title", "Developers and Contributors &middot; " . F3::get("Core")->getName());
        
        $developers = array();
        $contributors = array();
        foreach (F3::get("Core")->getAuthors() as $author) {
            if ($author->getType() == "0") {
                $developers[] = $author;
            } else {
                $contributors[] = $author;
            }
        }
        F3::set("developers", $developers);
        F3::set("contributors", $contributors);
        
        $template = new Template;
        echo $template->render("views/base.tpl");
    }
, 60);

F3::route("GET /feedback",
    function($f3) {
        F3::set("type", "feedback");
        F3::set("canonical", F3::get("domain") . "feedback");
        F3::set("title", "Feedback &middot; " . F3::get("Core")->getName());
        
        $template = new Template;
        echo $template->render("views/base.tpl");
    }
, 60);

F3::route("POST /feedback",
    function($f3) {
        F3::set("type", "feedback");
        F3::set("canonical", F3::get("domain") . "feedback");
        F3::set("title", "Feedback &middot; " . F3::get("Core")->getName());
        
		if (isset($_POST["issue"], $_POST["explanation"]) && !empty($_POST["issue"]) && !empty($_POST["explanation"])) {		
			$issueTypes = array(
				"timeline_typo",
				"browser_rendering",
				"website_content",
				"other"
			);
			
			if (!in_array($_POST["issue"], $issueTypes)) {
				$errors[] = "Please select a valid issue type.";
			}
			
			if (strlen($_POST["explanation"]) > 3000) {
				$errors[] = "Please make sure that your explanation isn't too long.";
			}
			
			if (empty($errors)) {
				$feedback_query = F3::get("DB")->prepare("INSERT INTO `feedback` (`issue`, `explanation`) VALUES (:issue, :explanation)");
				$feedback_query->bindValue(":issue", $_POST["issue"]);
				$feedback_query->bindValue(":explanation", $_POST["explanation"]);
				$feedback_result = $feedback_query->execute();
				
				if ($feedback_result) {
					F3::set("success", "Thank you, your feedback has been received and our administrators will now work to solve the problem shortly.");
				} else {
					$errors[] = "There was a MySQL error, please try again.";
				}
			}					
		}
		else {
			$errors[] = "Please make sure you selected an issue and filled out the explanation.";
		}
				
		if (!empty($errors)) {
			F3::set("errors", $errors);
		}
			
        $template = new Template;
        echo $template->render("views/base.tpl");
    }
);

F3::route("GET /opensearchdescription.xml",
    function($f3) {
        $template = new Template;
        echo $template->render("views/opensearchdescription.tpl", "application/xml");
    }
, 60);

F3::route("GET /robots.txt",
    function($f3) {
        $template = new Template;
        echo $template->render("views/robots.tpl", "text/plain");
    }
, 60);

F3::route("GET /sitemap.xml",
    function($f3) {
        $template = new Template;
        echo $template->render("views/sitemap.tpl", "application/xml");
    }
, 60);

F3::route("GET /admin",
	function ($f3) {
		session_start();
		F3::set("page", "Login");
		F3::set("title", "Admin Panel");
		if (isset($_SESSION["admin"], $_SESSION["id"]) && $_SESSION["admin"] != null && (int)$_SESSION["id"] <= 0) {
			F3::set("loggedIn", true);
			header("Location: /admin/home");
		}
		else {
			F3::set("loggedIn", false);
		}
		$errors = array();
		
		F3::set("errors", $errors);
		$template = new Template;
		echo $template->render("views/admin/base.tpl");
	}
, 60);

F3::route("GET /admin/home",
	function ($f3) {
		session_start();
		F3::set("page", "Home");
		F3::set("title", "Admin Panel");		
		$errors = array();
		
		if (isset($_SESSION["admin"], $_SESSION["id"]) && $_SESSION["admin"] != null && (int)$_SESSION["id"] > 0) {
			F3::set("loggedIn", true);
			
			F3::set("type", "home");
			F3::set("username", $_SESSION["admin"]);
		}
		else {
			F3::set("loggedIn", false);
			header("Location: /admin/login");
		}
		
		F3::set("errors", $errors);
		$template = new Template;
		echo $template->render("views/admin/base.tpl");
	}
, 60);

F3::route("GET /admin/logout",
	function ($f3) {
		session_start();
		F3::set("page", "Logout");
		F3::set("title", "Admin Panel");
		$errors = array();
		
		F3::set("loggedIn", false);
		if (isset($_SESSION["admin"], $_SESSION["id"]) && $_SESSION["admin"] != null && (int)$_SESSION["id"] > 0) {
			$_SESSION = array();
			session_destroy();
			F3::set("success", "You have been logged out.");
		}
		else {
			$errors[] = "You are not logged in.";
		}
		
		F3::set("errors", $errors);
		$template = new Template;
		echo $template->render("views/admin/base.tpl");
	}
, 60);

F3::route(
	array(
		"GET /admin/accounts",
		"POST /admin/accounts"
	),
	function ($f3) {
		session_start();
		F3::set("page", "Accounts");
		F3::set("title", "Admin Panel");		
		$errors = array();
		
		if (isset($_SESSION["admin"], $_SESSION["id"]) && $_SESSION["admin"] != null && (int)$_SESSION["id"] > 0) {
			F3::set("loggedIn", true);			
			F3::set("type", "accounts");
			F3::set("adminType", $_SESSION["type"]);
			F3::set("username", $_SESSION["admin"]);
			
			if (isset($_POST["form"]) && in_array($_POST["form"], array("add", "change"))) {
				if ($_POST["form"] == "add") {
					if ($_SESSION["type"] == 0) {
						if (isset($_POST["username"], $_POST["password"], $_POST["type"]) && !empty($_POST["username"]) && !empty($_POST["password"])) {
							$username = trim($_POST["username"]);
							$password = $_POST["password"];
							$type = (int)$_POST["type"];
							
							if (empty($username)) {
								$errors[] = "You cannot leave the username blank.";
							}
							
							if (empty($password)) {
								$errors[] = "You cannot leave the password blank.";
							}
							
							if ($type != 0 && $type != 1) {
								$errors[] = "Please choose a valid account type.";
							}
							
							if (count($errors) == 0) {
								$checkQuery = F3::get("DB")->prepare("SELECT `Username` FROM `admins` WHERE `Username`=:user");
								$checkQuery->bindValue(":user", $username);
								$checkQuery->execute();
								
								if (count($checkQuery->fetchAll()) > 0) {
									$errors[] = "That username is already in use.";
								}
								else {
									$addQuery = F3::get("DB")->prepare("INSERT INTO `admins` (`ID`,`Type`,`Username`,`Password`) VALUES (NULL, :type, :user, :pass)");
									$addQuery->bindValue(":type", $type);
									$addQuery->bindValue(":user", $username);
									$addQuery->bindValue(":pass", password_hash($password, PASSWORD_BCRYPT));
									$addQuery->execute();
									
									if ($addQuery) {
										F3::set("success", "New account was added.");
									}
									else {
										$errors[] = "There was a MySQL error, please try again.";
									}
								}
							}
						}
						else {
							$errors[] = "Please make sure to fill out all the fields.";
						}
					}
					else {
						$errors[] = "You are not allowed to do that.";
					}
				}
				elseif ($_POST["form"] == "change") {
					if ($_SESSION["type"] == 0) {
						if (isset($_POST["username"], $_POST["password"]) && !empty($_POST["username"]) && !empty($_POST["password"])) {
							$username = trim($_POST["username"]);
							$password = password_hash($_POST["password"], PASSWORD_BCRYPT);
							
							$updateQuery = F3::get("DB")->prepare("UPDATE `admins` SET `Password`=:pass WHERE `Username`=:user");
							$updateQuery->bindValue(":pass", $password);
							$updateQuery->bindValue(":user", $username);
							$updateQuery->execute();
							
							if ($updateQuery->rowCount() > 0) {
								F3::set("success", "Password updated for specified user.");
							}
							else {
								$errors[] = "Couldn't find the specified user.";
							}
						}
						else {
							$errors[] = "Please make sure to fill out both fields.";
						}
					}
					else {
						if (isset($_POST["oldpass"], $_POST["newpass"]) && !empty($_POST["oldpass"]) && !empty($_POST["newpass"])) {
							$currentPass = $_POST["oldpass"];
							$newPass = password_hash($_POST["newpass"], PASSWORD_BCRYPT);
							
							$curQuery = F3::get("DB")->prepare("SELECT `Password` FROM `admins` WHERE `ID`=:id");
							$curQuery->bindValue(":id", $_SESSION["id"]);
							$curQuery->execute();
							$queryResults = $curQuery->fetchAll();
							if (password_verify($currentPass, $queryResults[0]["Password"])) {
								$updateQuery = F3::get("DB")->prepare("UPDATE `admins` SET `Password`=:pass WHERE `ID`=:id");
								$updateQuery->bindValue(":pass", $newPass);
								$updateQuery->bindValue(":id", $_SESSION["id"]);
								$updateQuery->execute();
								
								if ($updateQuery) {
									F3::set("success", "Your password was changed.");
								}
								else {
									$errors[] = "There was a MySQL error, please try again.";
								}
							}
							else {
								$errors[] = "Your current password is incorrect.";
							}
						}
						else {
							$errors[] = "Please make sure to fill out both fields.";
						}
					}
				}
			}
		}
		else {
			F3::set("loggedIn", false);
			header("Location: /admin/login");
		}
		
		F3::set("errors", $errors);
		$template = new Template;
		echo $template->render("views/admin/base.tpl");
	}
);

F3::route(
	array(
		"POST /admin/login",
		"GET /admin/login"
	),
	function ($f3) {
		session_start();
		F3::set("page", "Login");
		F3::set("title", "Admin Panel");
		$errors = array();
		
		if (isset($_SESSION["admin"], $_SESSION["id"]) && $_SESSION["admin"] != null && (int)$_SESSION["id"] > 0) {
			F3::set("loggedIn", true);
			$errors[] = "You are already logged in.";
		}
		else {
			F3::set("loggedIn", false);			
			if (isset($_POST["username"], $_POST["password"]) && !empty($_POST["username"]) && !empty($_POST["password"])) {
				$username = trim($_POST["username"]);
				$password = $_POST["password"];
				
				$loginQuery = F3::get("DB")->prepare("SELECT `ID`,`Type`,`Username`,`Password` FROM `admins` WHERE `Username`=:user");
				$loginQuery->bindValue(":user", $username);
				$loginQuery->execute();
				$loginData = $loginQuery->fetchAll();
				if (count($loginData) <= 0) {
					$errors[] = "Invalid username or password.";
				}
				else {
					if (password_verify($password, $loginData[0]["Password"])) {
						$_SESSION["admin"] = $loginData[0]["Username"];
						$_SESSION["id"] = $loginData[0]["ID"];
						$_SESSION["type"] = $loginData[0]["Type"];
						if ($_SESSION["type"] === null) {
							$_SESSION["type"] = 1;
						}
						header("Location: /admin/home");
					}
					else {
						$errors[] = "Invalid username or password.";
					}
				}
			}
			else {
				if (count($_POST) > 0) {
					$errors[] = "Please fill out both your username and password.";
				}
			}
		}
		
		F3::set("errors", $errors);
		$template = new Template;
		echo $template->render("views/admin/base.tpl");
	}
);

F3::run();