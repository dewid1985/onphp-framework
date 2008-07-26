<?php
/***************************************************************************
 *   Copyright (C) 2008 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
	
	final class GoogleChart implements GoogleChartParameter
	{
		const BASE_URL = 'http://chart.apis.google.com/chart?';
		
		private $color 	= null;
		private $size 	= null;
		private $type 	= null;
		private $label 	= null;
		private $data 	= null;
		
		/**
		 * @return GoogleChart
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return GoogleChart
		**/
		public function setColor(GoogleChartColor $color)
		{
			$this->color = $color;
			
			return $this;
		}
		
		/**
		 * @return GoogleChart
		**/
		public function setSize(GoogleChartSize $size)
		{
			$this->size = $size;
			
			return $this;
		}
		
		/**
		 * @return GoogleChart
		**/
		public function setType(GoogleChartType $type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		/**
		 * @return GoogleChart
		**/
		public function setLabel(GoogleChartLabel $label)
		{
			$this->label = $label;
			
			return $this;
		}
		
		/**
		 * @return GoogleChart
		**/
		public function setData(GoogleChartData $data)
		{
			$this->data = $data;
			
			return $this;
		}
		
		public function toQueryString()
		{
			$url = self::BASE_URL;
			
			$parameters[] = $this->type->toQueryString();
			$parameters[] = $this->size->toQueryString();
			$parameters[] = $this->color->toQueryString();
			$parameters[] = $this->label->toQueryString();
			$parameters[] = $this->data->toQueryString();
			
			foreach ($parameters as $parameter) {
				$url .= $parameter.'&';
			}
			
			return $url;
		}
	}
?>