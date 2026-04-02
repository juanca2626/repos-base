<?php

namespace App\Http\Traits;

use App\Translation;

trait Translations
{
    /**
     * Save translations
     *
     * @param $translations
     * @param $type
     * @param $objectID
     */
    //Todo: Refactorizar funcion para mejorar el codigo
    public function saveTranslation($translations, $type = null, $objectID = null)
    {
        $translationActually = '';
        $slug = 'global';

        foreach ($translations as $key => $translation) {
            foreach ($translation as $vkey => $vtranslation) {
                if ($vkey !== 'id') {
                    $slug = $vkey;

                    break;
                }
            }

            if (empty($translation['id'])) {
                $translationObject = new Translation();
                $translationObject->type = $type;
                $translationObject->language_id = $key;
                $translationObject->object_id = $objectID;
                $translationObject->slug = $slug;
            } else {
                $translationObject = Translation::find($translation['id']);
            }

            if (empty($translation[$slug]) === false) {
                $translationActually = $translation[$slug];
                $translationObject->value = $translation[$slug];
            } else {
                $translationObject->value = $translationActually;
            }
            $translationObject->save();
        }
    }

    /**
     * Delete translations from Object ID
     *
     * @param $type
     * @param $objectID
     */
    public function deleteTranslation($type, $objectID)
    {
        Translation::where([['type', $type], ['object_id', (int)$objectID]])->delete();
    }

    /**
     * Save translations
     *
     * @param $translations
     * @param $type
     * @param $objectID
     */
    //Todo: Refactorizar funcion para mejorar el codigo
    public function saveMultipleTranslation($translations, $type = null, $objectID = null)
    {
        $translationActually = '';
        $slug = 'global';

        foreach ($translations as $key => $translation) {
            foreach ($translation as $vkey => $vtranslation) {
                foreach ($vtranslation as $ukey => $utranslation) {
                    if ($ukey !== 'id') {
                        $slug = $ukey;

                        break;
                    }
                }
                if (empty($vtranslation['id'])) {
                    $translationObject = new Translation();
                    $translationObject->type = $type;
                    $translationObject->language_id = $key;
                    $translationObject->object_id = $objectID;
                    $translationObject->slug = $slug;
                } else {
                    $translationObject = Translation::find($vtranslation['id']);
                }

                if (empty($vtranslation[$slug]) === false) {
                    $translationActually = $vtranslation[$slug];
                    $translationObject->value = $vtranslation[$slug];
                } else {
                    $translationObject->value = $translationActually;
                }
                $translationObject->save();
            }
        }
    }
}
