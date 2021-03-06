<?php

require 'constants.php';

class Deploy
{
	
	const URL = SCRUTINIZER_ENDPOINT . '?access_token=' . TOKEN;

	public function getLastBuild()
	{
		$curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, self::URL); 

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 

        $output = json_decode(curl_exec($curl)); 

        curl_close($curl);

        $lastCommit = $this->getLastCommit();
        
        $currentCommit = $this->saveCurrentCommit($output->_embedded->inspections[0]->metadata->source_reference);

        if ($lastCommit == $currentCommit) {
        	print "Already updated" . "\n";
        	exit;
        }

        return $output->_embedded->inspections[0]->build->status;
	}

	public function getLastCommit()
	{
		return fgets(fopen('commit.txt', 'r'));
	}

	public function saveCurrentCommit($commit)
	{
		$file = fopen("commit.txt", "w") or die("Unable to open file!");
				
		fwrite($file, $commit);

		fclose($file);

		return $commit;
	}

	public function run()
	{
		if ($this->getLastBuild() != "passed") {
			throw new Exception("Last Build Failed");			
		}
		
		shell_exec('cd ' . PATH_DEPLOY_SOURCE . ' && dep deploy:unlock -vvv');
		shell_exec('cd ' . PATH_DEPLOY_SOURCE . ' && dep deploy -vvv');
	}

}

$Deploy = new Deploy;

$Deploy->run();