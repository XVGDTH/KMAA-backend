<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Voucher, Course, Payment};
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function redeem(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string',
            'course_slug' => 'required|string|exists:courses,slug',
        ]);

        $course = Course::where('slug', $data['course_slug'])->firstOrFail();
        $voucher = Voucher::where('code', $data['code'])->where('active', true)->first();

        if (! $voucher || ($voucher->course_id && $voucher->course_id !== $course->id)
            || $voucher->used_count >= $voucher->max_uses
            || ($voucher->expires_at && $voucher->expires_at->isPast())) {
            return response()->json(['message' => 'Bon invalide ou expiré.'], 422);
        }

        $voucher->increment('used_count');

        Payment::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
            'amount' => 0,
            'provider' => 'bon',
            'status' => 'paye',
            'transaction_reference' => $voucher->code,
        ]);

        return response()->json(['message' => 'Bon validé.']);
    }
}