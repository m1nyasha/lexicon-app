<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Day;
use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

use App\Http\Resources\Word as WordResource;
use App\Http\Resources\WordCollection;

class WordController extends Controller
{
    public function store(Request $request)
    {

        $data = $request->all(['source', 'target']);

        if ($this->checkSourceWord($data["source"])) {
            return response()->json([
                "status" => false,
                "code" => 3,
                "errorMessage" => "Word already exist"
            ]);
        }

        $dateNow = Carbon::now()->format('Y-m-d');

        $dayId = !$this->checkDay($dateNow) ? $this->addDay() : $this->getNowDay();

        try {
            Word::create([
                "day_id" => $dayId,
                "source" => $data["source"],
                "target" => $data["target"],
            ]);
        } catch (\Exception $e) {
            Log::error('Error add new lang');
            Log::error($e->getMessage());
        }

        return response()->json([
            "status" => true
        ]);

    }

    public function get(Request $request)
    {
        $params = $request->all(['type', 'day']);

        $type = $params['type'] ?? 'random';

        if ($type === 'random') {

            $words = Word::inRandomOrder()
                ->where('day_id', $params['day'])
                ->first();

            return response()->json([
                "status" => true,
                "word" => new WordResource($words)
            ]);

        }

        return response()->json([
            "status" => false,
            "code" => 0,
            "message" => "Error params"
        ]);

    }

    private function checkSourceWord(string $word = null) :bool
    {
        return Word::where('source', $word)->exists();
    }

    private function checkDay(string $dataNow = null): bool
    {
        return Day::where('date_string', $dataNow)->exists();
    }

    private function addDay(): int
    {

        try {
            $day = Day::create([
                "date_string" => Carbon::now()->format('Y-m-d')
            ]);

            return $day->id;
        } catch (\Exception $e) {
            Log::error('Error add new day');
            Log::error($e->getMessage());
        }

        return 0;

    }

    private function getNowDay(): int
    {
        return Day::where('date_string', Carbon::now()->format('Y-m-d'))
            ->first()->id;
    }

}
