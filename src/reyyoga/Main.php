<?php

namespace reyyoga;

 use pocketmine\plugin\PluginBase;
 use pocketmine\player\Player; 
 use pocketmine\Server;
 use pocketmine\event\Listener;
 use pocketmine\event\player\PlayerJoinEvent;

 use pocketmine\command\Command;
 use pocketmine\command\CommandSender;

 use pocketmine\item\Item;

 use pocketmine\event\block\BlockPlaceEvent;
 use pocketmine\event\block\BlockBreakEvent;
 use pocketmine\block\Block;
 use pocketmine\block\BlockTypeIds;

 use pocketmine\utils\Config;
 use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener{

		private $particle = [];

	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder() . "topten_data");
		$this->saveResource("setting.yml");
		$this->config = (new Config($this->getDataFolder()."config.yml", Config::YAML))->getAll();
		if(empty($this->config["positions"])){
			$this->getServer()->getLogger()->Info("Please Set Location");
			return;
		}

		$pos = $this->config["positions"];
		$this->particle[] = new FloatingText($this, new Vector3($pos[0], $pos[1], $pos[2]));
		$this->getScheduler()->scheduleRepeatingTask(new UpdateTask($this), 40);
    	$this->getServer()->getLogger()->Info("Location Have Been Load");
    }

	public function onCommand(CommandSender $p, Command $command, string $label, array $args): bool{
		if($command->getName() === "settopmine"){
			if(!$p instanceof Player) return false;
			$config = new Config($this->getDataFolder()."config.yml", Config::YAML);
			$config->set("positions", [round($p->getPosition()->getX()), round($p->getPosition()->getY()), round($p->getPosition()->getZ())]);
			
			$config->save();
			
			$pluginManager = $this->getServer()->getPluginManager();
                        $topMinePlugin = $pluginManager->getPlugin("TopMineLeaderboard");
                        if ($topMinePlugin !== null) {
                        $pluginManager->disablePlugin($topMinePlugin);
                        $pluginManager->enablePlugin($topMinePlugin);
                        $p->sendMessage("§aSuccess: Add TopMine on your server!");
                   } else {
                        $p->sendMessage("§cError: TopMineLeaderboard plugin not found!");
                }
              }
		return true;
	}
	
	public function setfarmdata(BlockBreakEvent $event) {
		$player = $event->getPlayer();
		$name = $player->getName();
		$break = $event->getBlock();
		if($break->getTypeId() === BlockTypeIds::GOLD_ORE || $break->getTypeId() === BlockTypeIds::IRON_ORE || $break->getTypeId() === BlockTypeIds::COAL_ORE || $break->getTypeId() === BlockTypeIds::LAPIS_LAZULI_ORE || $break->getTypeId() === BlockTypeIds::DIAMOND_ORE || $break->getTypeId() === BlockTypeIds::REDSTONE_ORE || $break->getTypeId() === BlockTypeIds::EMERALD_ORE || $break->getTypeId() === BlockTypeIds::NETHER_QUARTZ_ORE){
			$data = new Config($this->getDataFolder() . "topten_data/topmine.yml", Config::YAML);
			$up = $data->get($name);
			$data->set($name, $up + 1);
			$data->save();
		}
	}

	public function createtopten(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$w = $this->getConfig()->get("world");
		$world = $player->getWorld()->getDisplayName() === "$w";
		$top = $this->getConfig()->get("enable");	

		if($world){
			if($top == "true"){
				$this->getLeaderBoard();
			}
		}
	}

	public function settopdata(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();		

		$farm = new Config($this->getDataFolder() . "topten_data/topmine.yml", Config::YAML);
		if(!$farm->exists($name)){
			$farm->set($name, 0);
			$farm->save();
		}
	}
	
	public function getLeaderBoard(): string{
    $data = new Config($this->getDataFolder() . "topten_data/topmine.yml", Config::YAML);
    $setting = new Config($this->getDataFolder() . "setting.yml", Config::YAML);
    $swallet = $data->getAll();
    $message = "";
    $top = $setting->get("title-lb");
    
    if (count($swallet) > 0) {
        arsort($swallet);
        $i = 1;
        foreach ($swallet as $name => $amount) {
            $tags = str_replace(["{num}", "{player}", "{amount}"], [$i, $name, $amount], $setting->get("text-lb")) . "\n";
            $message .= "\n ".$tags;
            
            if ($i >= 10) {
                break;
            }
            ++$i;
        }
    }
	$return = (string) $top.$message;
    	return $return;
}

	public function getParticles(): array{
		return $this->particle;
	}
}
