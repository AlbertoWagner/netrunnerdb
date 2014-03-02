<?php

namespace Netrunnerdb\CardsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Netrunnerdb\CardsBundle\Entity\Card;
use Netrunnerdb\CardsBundle\Entity\Pack;
use Netrunnerdb\CardsBundle\Entity\Cycle;
use Netrunnerdb\CardsBundle\Entity\Changelog;

class DefaultController extends Controller
{
	public function searchAction()
	{
		$dbh = $this->get('doctrine')->getConnection();
	
		$list_packs = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Pack')->findBy(array(), array("released" => "ASC", "number" => "ASC"));
		$packs = array();
		foreach($list_packs as $pack) {
			$packs[] = array(
					"name" => $pack->getName($this->getRequest()->getLocale()),
					"code" => $pack->getCode(),
			);
		}
	
		$list_cycles = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Cycle')->findBy(array(), array("number" => "ASC"));
		$cycles = array();
		foreach($list_cycles as $cycle) {
			$cycles[] = array(
					"name" => $cycle->getName($this->getRequest()->getLocale()),
					"code" => $cycle->getCode(),
			);
		}
	
		$list_types = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Type')->findBy(array(), array("name" => "ASC"));
		$types = array_map(function ($type) {
			return $type->getName();
		}, $list_types);
	
		$list_keywords = $dbh->executeQuery("SELECT DISTINCT c.keywords FROM card c WHERE c.keywords != ''")->fetchAll();
		$keywords = array();
		foreach($list_keywords as $keyword) {
			$subs = explode(' - ', $keyword["keywords"]);
			foreach($subs as $sub) {
				$keywords[$sub] = 1;
			}
		}
		$keywords = array_keys($keywords);
		sort($keywords);
	
		$list_illustrators = $dbh->executeQuery("SELECT DISTINCT c.illustrator FROM card c WHERE c.illustrator != '' ORDER BY c.illustrator")->fetchAll();
		$illustrators = array_map(function ($elt) {
			return $elt["illustrator"];
		}, $list_illustrators);
	
		$response = new Response();
		if($this->get('kernel')->getEnvironment() === 'optim') {
			$response->setPublic();
			$response->setMaxAge(600);
		} else {
			$response->setPrivate();
		}
	
		return $this->render('NetrunnerdbCardsBundle:Search:searchform.html.twig', array(
				"packs" => $packs,
				"cycles" => $cycles,
				"types" => $types,
				"keywords" => $keywords,
				"illustrators" => $illustrators,
				"allsets" => $this->allsets(),
				'locales' => $this->renderView('NetrunnerdbCardsBundle:Default:langs.html.twig'),
		), $response);
	}
	
	private function replaceSymbols($text)
	{
		$text = str_replace('[Subroutine]', '<span class="icon icon-subroutine"></span>', $text);
		$text = str_replace('[Credits]', '<span class="icon icon-credit"></span>', $text);
		$text = str_replace('[Trash]', '<span class="icon icon-trash"></span>', $text);
		$text = str_replace('[Click]', '<span class="icon icon-click"></span>', $text);
		$text = str_replace('[Recurring Credits]', '<span class="icon icon-recurring-credit"></span>', $text);
		$text = str_replace('[Memory Unit]', '<span class="icon icon-mu"></span>', $text);
		$text = str_replace('[Link]', '<span class="icon icon-link"></span>', $text);
		return $text;
	}
	
	function rulesAction()
	{
		static $sources;
		if(!$sources) $sources = array(1 => "Latest FAQ", 2 => "Direct answer");
		
		$response = new Response();
		if($this->get('kernel')->getEnvironment() === 'optim') {
			$response->setPublic();
			$response->setMaxAge(600);
		} else {
			$response->setPrivate();
		}
		
//		$list_rulings = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Rulings')->findBy(array(), array("created" => "ASC"));
		$rulings_faq = array();
		$rulings_mail = array();
/*		foreach($list_rulings as $ruling) {
			$r = array(
				"question" => $ruling->getQuestion(),
				"answer" => $ruling->getAnswer(),
				"created" => $ruling->getCreated()->format('Y-m-d')
			);
			if($ruling->getSource() == 1) {
				$rulings_faq[] = $r;
			} else {
				$rulings_mail[] = $r;
			}
		}
*/
		$page = $this->replaceSymbols($this->renderView('NetrunnerdbCardsBundle:Default:rules.html.twig', array(
			"faq" => $rulings_faq,
			"mail" => $rulings_mail,
		)));
		$response->setContent($page);
		return $response;
	}
	
	function aboutAction()
	{
		
		$response = new Response();
		if($this->get('kernel')->getEnvironment() === 'optim') {
			$response->setPublic();
			$response->setMaxAge(600);
		} else {
			$response->setPrivate();
		}
		
		return $this->render('NetrunnerdbCardsBundle:Default:about.html.twig', array(
		), $response);
	}

	function apidocAction()
	{
		
		$response = new Response();
		if($this->get('kernel')->getEnvironment() === 'optim') {
			$response->setPublic();
			$response->setMaxAge(600);
		} else {
			$response->setPrivate();
		}
		
		return $this->render('NetrunnerdbCardsBundle:Default:apidoc.html.twig', array(
		), $response);
	}

	private function allsetsdata()
	{
		$list_cycles = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Cycle')->findBy(array(), array("number" => "ASC"));
		$cycles = array();
		foreach($list_cycles as $cycle) {
			$packs = array();
			$sreal=0; $smax = 0;
			foreach($cycle->getPacks() as $pack) {
				$real = count($pack->getCards());
				$sreal += $real;
				$max = $pack->getSize();
				$smax += $max;
				$packs[] = array(
						"name" => $pack->getName($this->getRequest()->getLocale()),
						"code" => $pack->getCode(),
						"available" => $pack->getReleased() ? $pack->getReleased()->format('Y-m-d') : '',
						"known" => intval($real),
						"total" => $max,
						"url" => $this->get('router')->generate('netrunnerdb_netrunner_cards_list', array('pack_code' => $pack->getCode()), true),
						"search" => "e:".$pack->getCode(),
						"packs" => '',
				);
			}
			if(count($packs) == 1 && $packs[0]["name"] == $cycle->getName($this->getRequest()->getLocale())) {
				$cycles[] = $packs[0];
			} 
			else {
				$cycles[] = array(
						"name" => $cycle->getName($this->getRequest()->getLocale()),
						"code" => $cycle->getCode(),
						"known" => intval($sreal),
						"total" => $smax,
						"url" => $this->generateUrl('netrunnerdb_netrunner_cards_cycle', array('cycle_code' => $cycle->getCode()), true),
						"search" => 'c:'.$cycle->getCode(),
						"packs" => $packs,
				);
			}
		}
		return $cycles;
	}

	private function allsetsnocycledata()
	{
		$list_packs = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Pack')->findBy(array(), array("released" => "ASC", "number" => "ASC"));
		$packs = array();
		$sreal=0; $smax = 0;
		foreach($list_packs as $pack) {
			$real = count($pack->getCards());
			$sreal += $real;
			$max = $pack->getSize();
			$smax += $max;
			$packs[] = array(
					"name" => $pack->getName($this->getRequest()->getLocale()),
					"code" => $pack->getCode(),
					"number" => $pack->getNumber(),
					"cyclenumber" => $pack->getCycle()->getNumber(),
					"available" => $pack->getReleased() ? $pack->getReleased()->format('Y-m-d') : '',
					"known" => intval($real),
					"total" => $max,
					"url" => $this->get('router')->generate('netrunnerdb_netrunner_cards_list', array('pack_code' => $pack->getCode()), true),
			);
		}
		return $packs;
	}
	
	private function allsets() 
	{
		return $this->renderView('NetrunnerdbCardsBundle:Default:allsets.html.twig', array(
			"data" => $this->allsetsdata(),
		));
	}
	
	public function apisetsAction()
	{
		$response = new Response();
		$response->setPublic();
		$response->setMaxAge(600);
		$response->headers->add(array('Access-Control-Allow-Origin' => '*'));
		
		$jsonp = $this->getRequest()->query->get('jsonp');
		$locale = $this->getRequest()->query->get('_locale');
		if(isset($locale)) $this->getRequest()->setLocale($locale);
		
		$data = $this->allsetsnocycledata();
		
		$content = json_encode($data);
		if(isset($jsonp))
		{
			$content = "$jsonp($content)";
			$response->headers->set('Content-Type', 'application/javascript');
		} else
		{
			$response->headers->set('Content-Type', 'application/json');
		}
		$response->setContent($content);
		return $response;
	}
}
