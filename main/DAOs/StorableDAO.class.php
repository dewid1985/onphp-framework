<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup DAOs
	**/
	abstract class StorableDAO extends GenericDAO
	{
		public static function getIdName()
		{
			return 'id';
		}
		
		public function take(Identifiable $object)
		{
			return
				$object->getId()
					? $this->save($object)
					: $this->add($object);
		}
		
		public function add(Identifiable $object)
		{
			return
				$this->inject(
					OSQL::insert(),
					$object->setId(
						DBPool::getByDao($this)->obtainSequence(
							$this->getSequence()
						)
					)
				);
		}
		
		public function save(Identifiable $object)
		{
			return
				$this->inject(
					OSQL::update()->where(
						Expression::eqId($this->getIdName(), $object)
					),
					$object
				);
		}
		
		public function import(Identifiable $object)
		{
			return
				$this->inject(
					OSQL::insert(),
					$object
				);
		}
		
		public function guessAtom($atom, JoinCapableQuery $query)
		{
			if ($atom instanceof Property)
				return $this->mapProperty($atom);
			elseif (is_string($atom)) {
				if (strpos($atom, '.') !== false) {
					return
						$this->processPath(
							call_user_func(
								array($this->getObjectName(), 'proto')
							),
							$atom,
							$query
						);
				} elseif (array_key_exists($atom, $this->mapping))
					return $this->mapProperty(new Property($atom));
			} elseif ($atom instanceof LogicalObject)
				return $atom->toMapped($this, $query);
			elseif (
				($atom instanceof DBValue)
				|| ($atom instanceof DBField)
			) {
				return $atom;
			}
			
			return new DBValue($atom);
		}
		
		public function processPath(
			AbstractProtoClass $proto, $probablyPath, JoinCapableQuery $query
		)
		{
			$path = explode('.', $probablyPath);
			
			try {
				$property = $proto->getPropertyByName($path[0]);
			} catch (MissingElementException $e) {
				// oh, it's a value, not a property
				return new DBValue($probablyPath);
			}
			
			unset($path[0]);
			
			Assert::isTrue(
				$property->getRelationId() != null
				&& !$property->isGenericType()
			);
			
			if (
				$property->getRelationId() == MetaRelation::ONE_TO_MANY
				|| $property->getRelationId() == MetaRelation::MANY_TO_MANY
			) {
				$containerName = $property->getContainerName($this->getObjectName());
				$objectName = $property->getClassName();
				$dao = call_user_func(array($objectName, 'dao'));
				
				if ($property->getRelationId() == MetaRelation::MANY_TO_MANY) {
					$table =
						call_user_func(
							array($containerName, 'getHelperTable')
						);
					
					if (!$query->hasJoinedTable($table)) {
						$logic =
							Expression::eq(
								DBField::create(
									$this->getIdName(),
									$this->getTable()
								),
								
								DBField::create(
									call_user_func(
										array($containerName, 'getParentIdField')
									),
									$table
								)
							);
						
						if ($property->isRequired())
							$query->join($table, $logic);
						else
							$query->leftJoin($table, $logic);
					}
					
					$logic =
						Expression::eq(
							DBField::create(
								$dao->getIdName(),
								$dao->getTable()
							),
							
							DBField::create(
								call_user_func(
									array($containerName, 'getChildIdField')
								),
								$table
							)
						);
				} else {
					$logic =
						Expression::eq(
							DBField::create(
								call_user_func(
									array($containerName, 'getParentIdField')
								),
								$dao->getTable()
							),
							
							DBField::create(
								$this->getIdName(),
								$this->getTable()
							)
						);
				}
				
				if (!$query->hasJoinedTable($dao->getTable())) {
					if ($property->isRequired())
						$query->join($dao->getTable(), $logic);
					else
						$query->leftJoin($dao->getTable(), $logic);
				}
			} else { // OneToOne, LazyOneToOne
				$className = $property->getClassName();
				
				// prevents useless joins
				if (
					isset($path[1])
					&& ($path[1] == 'id')
					&& (count($path) == 1)
				)
					return
						new DBField(
							$property->getDumbIdName(),
							$this->getTable()
						);

				$dao = call_user_func(array($className, 'dao'));
				
				if (!$query->hasJoinedTable($dao->getTable())) {
					$logic =
						Expression::eq(
							DBField::create(
								$this->getFieldFor($property->getName()),
								$this->getTable()
							),
							
							DBField::create(
								$dao->getIdName(),
								$dao->getTable()
							)
						);
					
					if ($property->isRequired())
						$query->join($dao->getTable(), $logic);
					else
						$query->leftJoin($dao->getTable(), $logic);
				}
			}
			
			return $dao->guessAtom(implode('.', $path), $query);
		}
		
		protected function mapProperty(Property $property)
		{
			$name = $property->getName();
			
			Assert::isTrue(
				array_key_exists(
					$name,
					$this->mapping
				)
			);
			
			if ($this->mapping[$name] === null)
				return new DBField($name, $this->getTable());
			
			return new DBField($this->mapping[$name], $this->getTable());
		}

		protected function inject(
			InsertOrUpdateQuery $query, Identifiable $object
		)
		{
			$this->checkObjectType($object);
			
			DBPool::getByDao($this)->queryNull(
				$this->setQueryFields(
					$query->setTable($this->getTable()), $object
				)
			);
			
			$this->uncacheById($object->getId());
			
			// clean out Identifier, if any
			// and overwrite previous instances, if any
			return
				$this->identityMap[$object->getId()]
					= $object->setId($object->getId());
		}
	}
?>