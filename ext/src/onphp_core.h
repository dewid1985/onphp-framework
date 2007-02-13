/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#ifndef ONPHP_CORE_H
#define ONPHP_CORE_H

#include "php.h"

#include "onphp.h"

PHP_RINIT_FUNCTION(onphp_core);
PHP_MINIT_FUNCTION(onphp_core);
PHP_RSHUTDOWN_FUNCTION(onphp_core);

#endif /* ONPHP_CORE_H */
