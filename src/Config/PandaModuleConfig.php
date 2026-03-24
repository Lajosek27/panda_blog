<?php


declare(strict_types=1);

namespace Panda\Blog\Config;


class PandaModuleConfig extends AbstractModuleConfig
{
        protected $service_domain = 'panda_blog';
        protected $domain = 'Modules.Pandaoblog.';

        protected array $hooks =[
                'displayPandaBlog',
                'actionFrontControllerSetMedia',
                'moduleRoutes'
        ];
        protected array $installSqls = [
                "CREATE TABLE IF NOT EXISTS `_PREFIX_panda_blog_category` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                        `name` VARCHAR(255) NOT NULL,
                        `slug` VARCHAR(255) NOT NULL,
                        `parent` INT UNSIGNED DEFAULT NULL,
                        `description` TEXT DEFAULT NULL,
                        `meta_title` VARCHAR(255) DEFAULT NULL,
                        `meta_description` VARCHAR(512) DEFAULT NULL,
                        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                        `position` INT NOT NULL DEFAULT 0,
                        `date_add` DATETIME NOT NULL,
                        `date_upd` DATETIME NOT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `uniq_slug` (`slug`),
                        KEY `idx_parent` (`parent`),
                        CONSTRAINT `fk_panda_blog_category_parent`
                        FOREIGN KEY (`parent`) REFERENCES `_PREFIX_panda_blog_category` (`id`) ON DELETE SET NULL
                ) ENGINE=_ENGINE_ DEFAULT CHARSET=_CHARSET_",

                "CREATE TABLE IF NOT EXISTS `_PREFIX_panda_blog_post` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                        `title` VARCHAR(255) NOT NULL,
                        `slug` VARCHAR(255) NOT NULL,
                        `main_category_id` INT UNSIGNED DEFAULT NULL,
                        `excerpt` TEXT DEFAULT NULL,
                        `content` TEXT DEFAULT NULL,
                        `image` VARCHAR(255) DEFAULT NULL,
                        `author` VARCHAR(255) NOT NULL,
                        `meta_title` VARCHAR(255) DEFAULT NULL,
                        `meta_description` VARCHAR(512) DEFAULT NULL,
                        `related_product_ids` JSON NOT NULL DEFAULT ('[]'),
                        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                        `date_add` DATETIME NOT NULL,
                        `date_upd` DATETIME NOT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `uniq_post_slug` (`slug`),
                        KEY `idx_main_category` (`main_category_id`),
                        CONSTRAINT `fk_panda_blog_post_main_category`
                        FOREIGN KEY (`main_category_id`) REFERENCES `_PREFIX_panda_blog_category` (`id`) ON DELETE SET NULL
                ) ENGINE=_ENGINE_ DEFAULT CHARSET=_CHARSET_",

                "CREATE TABLE IF NOT EXISTS `_PREFIX_panda_blog_post_category` (
                        `post_id` INT UNSIGNED NOT NULL,
                        `category_id` INT UNSIGNED NOT NULL,
                        PRIMARY KEY (`post_id`, `category_id`),
                        KEY `idx_panda_blog_post_category_post` (`post_id`),
                        KEY `idx_panda_blog_post_category_category` (`category_id`),
                        CONSTRAINT `fk_panda_blog_post_category_post`
                        FOREIGN KEY (`post_id`) REFERENCES `_PREFIX_panda_blog_post` (`id`) ON DELETE CASCADE,
                        CONSTRAINT `fk_panda_blog_post_category_category`
                        FOREIGN KEY (`category_id`) REFERENCES `_PREFIX_panda_blog_category` (`id`) ON DELETE CASCADE
                ) ENGINE=_ENGINE_ DEFAULT CHARSET=_CHARSET_",
        ];
        public function install(): bool
        {
                if (!$this->installBlogTab()) {
                        return false;
                }
                return parent::install();
        }

        private function installBlogTab()
        {
                $parentClass = 'AdminPandaBlogParent';
                $parentTabId = \Tab::getIdFromClassName($parentClass);
                $parentTab = new \Tab($parentTabId ?: null);
                $parentTab->active = true;
                $parentTab->class_name = $parentClass;
                $parentTab->id_parent = 0;
                $parentTab->module = $this->getServiceDomain();
                $parentTab->wording = 'Blog';
                $parentTab->wording_domain = 'Module.Pandablog.Admin';
                foreach (\Language::getLanguages() as $lang) {
                        $parentTab->name[$lang['id_lang']] = "Blog";
                }
                if (!$parentTab->save()) {
                        return false;
                }


                $childClass2 = 'PandaBlogPost';
                $childTabId2 = \Tab::getIdFromClassName($childClass2);
                $childTab2 = new \Tab($childTabId2 ?: null);
                $childTab2->active = true;
                $childTab2->class_name = $childClass2;
                $childTab2->id_parent = (int) \Tab::getIdFromClassName($parentClass);
                $childTab2->route_name = 'panda_blog_post.list';
                $childTab2->module = $this->getServiceDomain();
                $childTab2->wording = 'Wpisy';
                $childTab2->wording_domain = 'Module.Pandablog.Admin';
                $childTab2->icon = 'edit';
                /** @var array{'id_lang': int, "locale": string} $lang */
                foreach (\Language::getLanguages() as $lang) {
                        $childTab2->name[$lang['id_lang']] = 'Wpisy';
                }
                if (!$childTab2->save()) {
                        return false;
                }



                $childClass1 = 'PandaBlogCategory';
                $childTabId1 = \Tab::getIdFromClassName($childClass1);
                $childTab1 = new \Tab($childTabId1 ?: null);
                $childTab1->active = true;
                $childTab1->class_name = $childClass1;
                $childTab1->id_parent = (int) \Tab::getIdFromClassName($parentClass);
                $childTab1->route_name = 'panda_blog_category.list';
                $childTab1->module = $this->getServiceDomain();
                $childTab1->wording = 'Kategorie';
                $childTab1->wording_domain = 'Module.Pandablog.Admin';
                $childTab1->icon = 'category';
                /** @var array{'id_lang': int, "locale": string} $lang */
                foreach (\Language::getLanguages() as $lang) {
                        $childTab1->name[$lang['id_lang']] = 'Kategorie';
                }
                if (!$childTab1->save()) {
                        return false;
                }

                $childClass = 'AdminPandaBlogConfiguration';
                $childTabId = \Tab::getIdFromClassName($childClass);
                $childTab = new \Tab($childTabId ?: null);
                $childTab->active = true;
                $childTab->class_name = $childClass;
                $childTab->id_parent = (int) \Tab::getIdFromClassName($parentClass);
                $childTab->route_name = 'panda_blog_configuration';
                $childTab->module = $this->getServiceDomain();
                $childTab->wording = 'Ustawienia';
                $childTab->wording_domain = 'Module.Pandablog.Admin';
                $childTab->icon = 'settings';
                /** @var array{'id_lang': int, "locale": string} $lang */
                foreach (\Language::getLanguages() as $lang) {
                        $childTab->name[$lang['id_lang']] = 'Ustawienia';
                }
                if (!$childTab->save()) {
                        return false;
                }



                return true;
        }
}
