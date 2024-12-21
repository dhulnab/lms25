<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book_borrowing;
use App\Models\Borrow_request;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookBorrowingController extends Controller
{
    // public function returnCopy($id)
    // {
    //     $borrowingRecord = Book_borrowing::find($id);
    //     if (!$borrowingRecord) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'copy not found'
    //         ], 404);
    //     }
    //     DB::beginTransaction();
    //     try {
    //         // $borrowingRecord->update(['returned' => true]);

    //         // if ($borrowingRecord->book_for_borrow_copy()) {

    //         // }
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //     }



    //     return response()->json([
    //         'success' => true,
    //         'message' => [$borrowingRecord->book_for_borrow_copy()]
    //     ]);
    // }





    public function receiveBookCopy(Request $request)
    {
        $validatedData = $request->validate([
            'book_copy_id' => 'required|integer|exists:book_for_borrow_copies,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Fetch the borrow request
        $borrowRequest = Borrow_request::with(['book_for_borrow_copy'])
            ->where('user_id', $validatedData['user_id'])
            ->where('copy_id', $validatedData['book_copy_id'])
            ->where('status', 'approved')
            ->where('borrow_start_date', '>=', now())
            ->first();

        // Check if the borrow request exists
        if (!$borrowRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Borrow request not found!',
            ], 404);
        }
        // Create a new borrowing record
        $borrowingRecord = Book_borrowing::create([
            'user_id' => $validatedData['user_id'],
            'book_copy_id' => $validatedData['book_copy_id'],
            'borrow_start' => $borrowRequest->borrow_start_date,
            'borrow_end' => $borrowRequest->borrow_end_date,
        ]);

        // Update the book copy status
        $borrowRequest->book_for_borrow_copy->update(['status' => 'borrowed']);
        $borrowRequest->status = 'rejected';
        $borrowRequest->save();

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Borrowing record added successfully!',
            'data' => $borrowingRecord,
        ], 201);
    }
}
