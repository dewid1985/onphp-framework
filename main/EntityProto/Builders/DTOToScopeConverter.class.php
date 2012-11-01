<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp;

	final class DTOToScopeConverter extends PrototypedBuilder
	{
		/**
		 * @return \Onphp\DTOToScopeConverter
		**/
		public static function create(EntityProto $proto)
		{
			return new self($proto);
		}
		
		protected function createEmpty()
		{
			return array();
		}
		
		/**
		 * @return \Onphp\DTOGetter
		**/
		protected function getGetter($object)
		{
			return new DTOGetter($this->proto, $object);
		}
		
		/**
		 * @return \Onphp\ScopeSetter
		**/
		protected function getSetter(&$object)
		{
			return new ScopeSetter($this->proto, $object);
		}
	}
?>