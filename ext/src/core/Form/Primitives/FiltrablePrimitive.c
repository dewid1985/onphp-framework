/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#include "onphp.h"

#include "core/Exceptions.h"

#include "core/Form/Filters/Filtrator.h"

#include "core/Form/Primitives/BasePrimitive.h"
#include "core/Form/Primitives/FiltrablePrimitive.h"

ONPHP_METHOD(FiltrablePrimitive, __construct)
{
	zval *name, *importFilter, *displayFilter;
	
	ONPHP_GET_ARGS("z", &name);
	
	ONPHP_MAKE_FOREIGN_OBJECT("FilterChain", importFilter);
	
	ALLOC_INIT_ZVAL(displayFilter);
	ZVAL_ZVAL(displayFilter, importFilter, 1, 0);
	
	ONPHP_UPDATE_PROPERTY(getThis(), "importFilter", importFilter);
	ONPHP_UPDATE_PROPERTY(getThis(), "displayFilter", displayFilter);
	
	zend_call_method_with_1_params(
		&getThis(),
		onphp_ce_BasePrimitive,
		&onphp_ce_BasePrimitive->constructor,
		"__construct",
		NULL,
		name
	);
}

ONPHP_METHOD(FiltrablePrimitive, __destruct)
{
	ONPHP_PROPERTY_DESTRUCT(importFilter);
	ONPHP_PROPERTY_DESTRUCT(displayFilter);
}

ONPHP_GETTER(FiltrablePrimitive, getDisplayFilter, displayFilter);
ONPHP_GETTER(FiltrablePrimitive, getImportFilter, importFilter);

ONPHP_SETTER(FiltrablePrimitive, setDisplayFilter, displayFilter);
ONPHP_SETTER(FiltrablePrimitive, setImportFilter, importFilter);

#define ONPHP_FILTRABLE_PRIMITIVE_ADD_FILTER(method_name, property_name)	\
ONPHP_METHOD(FiltrablePrimitive, method_name)								\
{																			\
	zval																	\
		*filter,															\
		*chain = ONPHP_READ_PROPERTY(getThis(), # property_name);			\
																			\
	ONPHP_GET_ARGS("z", &filter);											\
																			\
	ONPHP_CALL_METHOD_1(chain, "add", NULL, filter);						\
																			\
	RETURN_THIS;															\
}

ONPHP_FILTRABLE_PRIMITIVE_ADD_FILTER(addDisplayFilter, displayFilter);
ONPHP_FILTRABLE_PRIMITIVE_ADD_FILTER(addImportFilter, importFilter);

#undef ONPHP_FILTRABLE_PRIMITIVE_ADD_FILTER

#define ONPHP_FILTRABLE_PRIMITIVE_CHAIN_DROP(method_name, property_name)	\
ONPHP_METHOD(FiltrablePrimitive, method_name)								\
{																			\
	zval *chain;															\
																			\
	chain = ONPHP_READ_PROPERTY(getThis(), # property_name);				\
																			\
	ZVAL_FREE(chain);														\
																			\
	ONPHP_MAKE_FOREIGN_OBJECT("FilterChain", chain);						\
																			\
	ONPHP_UPDATE_PROPERTY(getThis(), # property_name, chain);				\
}

ONPHP_FILTRABLE_PRIMITIVE_CHAIN_DROP(dropDisplayFilters, displayFilter);
ONPHP_FILTRABLE_PRIMITIVE_CHAIN_DROP(dropImportFilters, importFilter);

#undef ONPHP_FILTRABLE_PRIMITIVE_CHAIN_DROP

#define ONPHP_FILTRABLE_PRIMITIVE_APPLY_FILTERS								\
	if (Z_TYPE_P(value) == IS_ARRAY) {										\
		zval *element;														\
																			\
		ONPHP_FOREACH(value, element) {										\
			ONPHP_CALL_METHOD_1(chain, "apply", &element, element);			\
		}																	\
	} else {																\
		ONPHP_CALL_METHOD_1(chain, "apply", &value, value);					\
	}

ONPHP_METHOD(FiltrablePrimitive, getDisplayValue)
{
	zval
		*value,
		*chain = ONPHP_READ_PROPERTY(getThis(), "displayFilter");
	
	ONPHP_CALL_METHOD_0(getThis(), "getactualvalue", &value);
	
	ONPHP_FILTRABLE_PRIMITIVE_APPLY_FILTERS;
	
	RETURN_ZVAL(value, 1, 0);
}

ONPHP_METHOD(FiltrablePrimitive, selfFilter)
{
	zval
		*value = ONPHP_READ_PROPERTY(getThis(), "value"),
		*chain = ONPHP_READ_PROPERTY(getThis(), "importFilter");
	
	ONPHP_FILTRABLE_PRIMITIVE_APPLY_FILTERS;
	
	RETURN_THIS;
}

#undef ONPHP_FILTRABLE_PRIMITIVE_APPLY_FILTERS

static ONPHP_ARGINFO_ONE;
static ONPHP_ARGINFO_FILTRATOR;
static ONPHP_ARGINFO_FILTER_CHAIN;

zend_function_entry onphp_funcs_FiltrablePrimitive[] = {
	ONPHP_ME(FiltrablePrimitive, __construct, arginfo_one, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	ONPHP_ME(FiltrablePrimitive, __destruct, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_DTOR)
	ONPHP_ME(FiltrablePrimitive, setDisplayFilter, arginfo_filter_chain, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, addDisplayFilter, arginfo_filtrator, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, dropDisplayFilters, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, getDisplayValue, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, setImportFilter, arginfo_filter_chain, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, addImportFilter, arginfo_filtrator, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, getDisplayFilter, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, getImportFilter, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ME(FiltrablePrimitive, selfFilter, NULL, ZEND_ACC_PROTECTED)
	{NULL, NULL, NULL}
};