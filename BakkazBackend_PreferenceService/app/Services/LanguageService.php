<?php

namespace App\Services;

use App\Helpers\ResponseHelpers;
use Illuminate\Http\Request;
use App\Interfaces\LanguageInterface;
use App\Models\Language;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LanguageService {
    static public function createLanguage(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'language' => 'required|string',
                'language_code' => 'required|string'
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            // Check if Language already exists
            $exists = Language::where("language_code", $request->input('language_code'))->first();

            if (!empty($exists)) {
                return responseHelpers::conflict();
            }


            $language = Language::create([
                "language" => $request->input('language'),
                "language_code" => $request->input('language_code'),
            ]);

            if (!empty($language)) {
                return ResponseHelpers::success(message: "Language Created Successfully");
            }

            return ResponseHelpers::error("Error Creating Language");


        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "language", "language_code"
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(message: $th->getMessage());
        }

    }

    static public function showLanguages(Request $request) {

       $languages = Language::get();

       if (!empty($languages)) {
        return ResponseHelpers::success(data: $languages->toArray());
       }

       return ResponseHelpers::notFound(message: "Languages Not Found");
    }
}