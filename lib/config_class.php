<?php

abstract class Config{
    const SITE_NAME = "Engine CMS";
    const SECRET = "DGLJDG5";
    const ADDRESS = "http://engine.local";
    const ADM_NAME = "Владислав Колодка";
    const ADM_EMAIL = "vladkolodka@gmail.com";

    const DB_HOST = "localhost";
    const DB_USER = "root";
    const DB_PASSWORD = "root";
    const DB_NAME = "engine";
    const DB_PREFIX = "eng_";
    const DB_SYM_QUERY = "?";

    const DB_IMG = "/images/";
    const DB_IMG_ARTICLES = "/images/articles/";
    const DB_IMG_AVATAR = "/images/avatar/";
    const DIR_TMPL = "W:\\domains\\engine.local\\tmpl\\";
    const DIR_EMAILS = "W:\\domains\\engine.local\\tmpl\\emails\\";

    const FILE_MESSAGES = "W:\\domains\\engine.local\\text\\messages.ini";

    const FORMAT_DATE = "%d.%m.%Y %H:%M:%S";

    const COUNT_ARTICLES_ON_PAGE = 3;
    const COUNT_SHOW_PAGES = 10;

    const MIN_SEARCH_LEN = 3;
    const LEN_SEARCH_RES = 255; // краткое описание результата в поиске

    const DEFAULT_AVATAR = "default.png";
    const MAX_SIZE_AVATAR = 51200;
}