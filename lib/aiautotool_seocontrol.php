<?php
defined('ABSPATH') or die();
class AIAutoTool_SEO {

    public function set_title($postId, $newTitle) {
        $seoPlugin = $this->detectActiveSEOPlugin();

        switch ($seoPlugin) {
            case 'yoast':
                $this->setYoastTitle($postId, $newTitle);
                break;
            case 'allinone':
                $this->setAllInOneTitle($postId, $newTitle);
                break;
            case 'rankmath':
                $this->setRankMathTitle($postId, $newTitle);
                break;
            default:
                // Do nothing if no supported SEO plugin is active
                break;
        }
    }

    public function set_desc($postId, $newDesc) {
        $seoPlugin = $this->detectActiveSEOPlugin();

        switch ($seoPlugin) {
            case 'yoast':
                $this->setYoastDescription($postId, $newDesc);
                break;
            case 'allinone':
                $this->setAllInOneDescription($postId, $newDesc);
                break;
            case 'rankmath':
                $this->setRankMathDescription($postId, $newDesc);
                break;
            default:
                // Do nothing if no supported SEO plugin is active
                break;
        }
    }

    private function detectActiveSEOPlugin() {
        // Check if Yoast SEO is active
        if (class_exists('WPSEO_Options')) {
            return 'yoast';
        }

        // Check if All in One SEO is active
        if (defined('AIOSEOP_VERSION')) {
            return 'allinone';
        }

        // Check if Rank Math SEO is active
        if (defined('RANK_MATH_VERSION')) {
            return 'rankmath';
        }

        // No supported SEO plugin is active
        return 'none';
    }

    private function setYoastTitle($postId, $newTitle) {
        if (class_exists('WPSEO_Meta') && method_exists('WPSEO_Meta', 'get_instance')) {
            $seoMeta = WPSEO_Meta::get_instance($postId);
            $seoMeta->title = $newTitle;
            $seoMeta->save();
        }
    }

    private function setAllInOneTitle($postId, $newTitle) {
        if (class_exists('All_in_One_SEO_Pack')) {
            $aiosp = new All_in_One_SEO_Pack();
            $aiosp->add_post_custom_values($postId, array('title' => $newTitle));
        }
    }

    private function setRankMathTitle($postId, $newTitle) {
        if (function_exists('rank_math')) {
            $title = rank_math_replace_vars(get_post_meta($postId, 'rank_math_title', true), $postId);
            update_post_meta($postId, 'rank_math_title', $newTitle);
        }
    }

    private function setYoastDescription($postId, $newDesc) {
        if (class_exists('WPSEO_Meta') && method_exists('WPSEO_Meta', 'get_instance')) {
            $seoMeta = WPSEO_Meta::get_instance($postId);
            $seoMeta->description = $newDesc;
            $seoMeta->save();
        }
    }

    private function setAllInOneDescription($postId, $newDesc) {
        if (class_exists('All_in_One_SEO_Pack')) {
            $aiosp = new All_in_One_SEO_Pack();
            $aiosp->add_post_custom_values($postId, array('description' => $newDesc));
        }
    }

    private function setRankMathDescription($postId, $newDesc) {
        if (function_exists('rank_math')) {
            $description = rank_math_replace_vars(get_post_meta($postId, 'rank_math_description', true), $postId);
            update_post_meta($postId, 'rank_math_description', $newDesc);
        }
    }


    // set title desc for cate & tag

    public function set_cate_title($categoryId, $newTitle) {
        $seoPlugin = $this->detectActiveSEOPlugin();

        switch ($seoPlugin) {
            case 'yoast':
                $this->setYoastCategoryTitle($categoryId, $newTitle);
                break;
            case 'allinone':
                $this->setAllInOneCategoryTitle($categoryId, $newTitle);
                break;
            case 'rankmath':
                $this->setRankMathCategoryTitle($categoryId, $newTitle);
                break;
            default:
                // Do nothing if no supported SEO plugin is active
                break;
        }
    }

    public function set_tag_title($tagId, $newTitle) {
        $seoPlugin = $this->detectActiveSEOPlugin();

        switch ($seoPlugin) {
            case 'yoast':
                $this->setYoastTagTitle($tagId, $newTitle);
                break;
            case 'allinone':
                $this->setAllInOneTagTitle($tagId, $newTitle);
                break;
            case 'rankmath':
                $this->setRankMathTagTitle($tagId, $newTitle);
                break;
            default:
                // Do nothing if no supported SEO plugin is active
                break;
        }
    }

    private function setYoastCategoryTitle($categoryId, $newTitle) {
        if (class_exists('WPSEO_Taxonomy')) {
            $seoTerm = WPSEO_Taxonomy::get_instance('category', $categoryId);
            $seoTerm->title = $newTitle;
            $seoTerm->save();
        }
    }

    private function setAllInOneCategoryTitle($categoryId, $newTitle) {
        if (class_exists('All_in_One_SEO_Pack')) {
            $aiosp = new All_in_One_SEO_Pack();
            $aiosp->add_taxonomy_custom_values($categoryId, array('title' => $newTitle));
        }
    }

    private function setRankMathCategoryTitle($categoryId, $newTitle) {
        if (function_exists('rank_math')) {
            $title = rank_math_replace_vars(get_term_meta($categoryId, 'rank_math_title', true), $categoryId);
            update_term_meta($categoryId, 'rank_math_title', $newTitle);
        }
    }

    private function setYoastTagTitle($tagId, $newTitle) {
        if (class_exists('WPSEO_Taxonomy')) {
            $seoTerm = WPSEO_Taxonomy::get_instance('post_tag', $tagId);
            $seoTerm->title = $newTitle;
            $seoTerm->save();
        }
    }

    private function setAllInOneTagTitle($tagId, $newTitle) {
        if (class_exists('All_in_One_SEO_Pack')) {
            $aiosp = new All_in_One_SEO_Pack();
            $aiosp->add_taxonomy_custom_values($tagId, array('title' => $newTitle));
        }
    }

    private function setRankMathTagTitle($tagId, $newTitle) {
        if (function_exists('rank_math')) {
            $title = rank_math_replace_vars(get_term_meta($tagId, 'rank_math_title', true), $tagId);
            update_term_meta($tagId, 'rank_math_title', $newTitle);
        }
    }

    public function set_cate_desc($categoryId, $newDesc) {
        $seoPlugin = $this->detectActiveSEOPlugin();

        switch ($seoPlugin) {
            case 'yoast':
                $this->setYoastCategoryDescription($categoryId, $newDesc);
                break;
            case 'allinone':
                $this->setAllInOneCategoryDescription($categoryId, $newDesc);
                break;
            case 'rankmath':
                $this->setRankMathCategoryDescription($categoryId, $newDesc);
                break;
            default:
                // Do nothing if no supported SEO plugin is active
                break;
        }
    }

    public function set_tag_desc($tagId, $newDesc) {
        $seoPlugin = $this->detectActiveSEOPlugin();

        switch ($seoPlugin) {
            case 'yoast':
                $this->setYoastTagDescription($tagId, $newDesc);
                break;
            case 'allinone':
                $this->setAllInOneTagDescription($tagId, $newDesc);
                break;
            case 'rankmath':
                $this->setRankMathTagDescription($tagId, $newDesc);
                break;
            default:
                // Do nothing if no supported SEO plugin is active
                break;
        }
    }

    // ... (Các phương thức khác không thay đổi)

    private function setYoastCategoryDescription($categoryId, $newDesc) {
        if (class_exists('WPSEO_Taxonomy')) {
            $seoTerm = WPSEO_Taxonomy::get_instance('category', $categoryId);
            $seoTerm->description = $newDesc;
            $seoTerm->save();
        }
    }

    private function setAllInOneCategoryDescription($categoryId, $newDesc) {
        if (class_exists('All_in_One_SEO_Pack')) {
            $aiosp = new All_in_One_SEO_Pack();
            $aiosp->add_taxonomy_custom_values($categoryId, array('description' => $newDesc));
        }
    }

    private function setRankMathCategoryDescription($categoryId, $newDesc) {
        if (function_exists('rank_math')) {
            $description = rank_math_replace_vars(get_term_meta($categoryId, 'rank_math_description', true), $categoryId);
            update_term_meta($categoryId, 'rank_math_description', $newDesc);
        }
    }

    private function setYoastTagDescription($tagId, $newDesc) {
        if (class_exists('WPSEO_Taxonomy')) {
            $seoTerm = WPSEO_Taxonomy::get_instance('post_tag', $tagId);
            $seoTerm->description = $newDesc;
            $seoTerm->save();
        }
    }

    private function setAllInOneTagDescription($tagId, $newDesc) {
        if (class_exists('All_in_One_SEO_Pack')) {
            $aiosp = new All_in_One_SEO_Pack();
            $aiosp->add_taxonomy_custom_values($tagId, array('description' => $newDesc));
        }
    }

    private function setRankMathTagDescription($tagId, $newDesc) {
        if (function_exists('rank_math')) {
            $description = rank_math_replace_vars(get_term_meta($tagId, 'rank_math_description', true), $tagId);
            update_term_meta($tagId, 'rank_math_description', $newDesc);
        }
    }
}


