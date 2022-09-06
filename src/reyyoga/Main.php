<?php

namespace reyyoga;

 use pocketmine\plugin\PluginBase;
 use pocketmine\Player; 
 use pocketmine\Server;
 use pocketmine\event\Listener;
 use pocketmine\event\player\PlayerJoinEvent;
 
 use pocketmine\command\Command;
 use pocketmine\command\CommandSender;
 
 use pocketmine\item\Item;
 use pocketmine\event\block\BlockPlaceEvent;
 use pocketmine\event\block\BlockBreakEvent;
 
 use pocketmine\block\Block;
 
 use pocketmine\utils\Config;
 use pocketmine\math\Vector3;
 
class Main extends PluginBase implements Listener{
	
	private $particle = [];

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder() . "topten_data");
		$this->config = (new Config($this->getDataFolder()."config.yml", Config::YAML))->getAll();
		if(empty($this->config["positions"])){
			$this->getServer()->getLogger()->Info("Set Location");
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
			if(!$p->isOp()) return false;
			$config = new Config($this->getDataFolder()."config.yml", Config::YAML);
			$config->set("positions", [round($p->getX()), round($p->getY()), round($p->getZ())]);
			$config->save();
			$p->sendMessage("§a* §oBerhasil menentukan lokasi daftar TopMine§r§f!");
		}
		return true;
	}
	
	public function setfarmdata(BlockBreakEvent $event) {
		$player = $event->getPlayer();
		$name = $player->getName();
		$break = $event->getBlock();
		if($break->getId() === 14 || $break->getId() === 15 || $break->getId() === 16 || $break->getId() === 21 || $break->getId() === 56 || $break->getId() === 56 || $break->getId() === 73 || $break->getId() === 129 ||$break->getId() === 153){
			$data = new Config($this->getDataFolder() . "topten_data/topfarm.yml", Config::YAML);
			$up = $data->get($name);
			$data->set($name, $up + 0.1);
			$data->save();
		}
	}
	
	public function createtopten(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$w = $this->getConfig()->get("world");
		$world = $player->getLevel()->getName() === "$w";
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
		
		$farm = new Config($this->getDataFolder() . "topten_data/topfarm.yml", Config::YAML);
		if(!$farm->exists($name)){
			$farm->set($name, 0);
			$farm->save();
		}
	}
	
	public function getLeaderBoard(): string{
		$data = new Config($this->getDataFolder() . "topten_data/topfarm.yml", Config::YAML);
		$swallet = $data->getAll();
		$message = "";
		$top = "§d§l§oTopMining";
		if(count($swallet) > 0){
    		arsort($swallet);
    		$i = 1;
			foreach ($swallet as $name => $amount) {
				$message .= "\n ".$i.". §7".$name."  §akeep  §f".$amount." §aores\n";
				if($i >= 10){
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
