<?php

/**
 * Sends HTTP headers to forbid transparent proxies, HTTP/1.x proxies to
 * to cache answers from the server running NVLL.
 *
 * This is quite aggressive, we could have set Cache-control to private
 * to forbid only proxy to cache answers but this would allow browser
 * to be able to cache answers; given that some people use NVLL from
 * a public computer, Cache-control is set to no-cache to prevent any
 * caching. This might lower NVLL speed but it's hard to be both secure
 * and cache-friendly when dealing with dynamic content.
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

header("Pragma: no-cache");
header("Cache-control: no-cache");
