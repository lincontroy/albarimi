<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ViewsController extends Controller
{
    public function index()
    {
        // Sample data for demonstration
        $viewsData = [
            'views' => [
                ['id' => '001', 'views_count' => 150, 'earned' => 75, 'phone' => '+254712345678', 'date' => '2024-01-15', 'status' => 'completed'],
                ['id' => '002', 'views_count' => 200, 'earned' => 100, 'phone' => '+254723456789', 'date' => '2024-01-14', 'status' => 'pending'],
                ['id' => '003', 'views_count' => 100, 'earned' => 50, 'phone' => '+254734567890', 'date' => '2024-01-13', 'status' => 'completed'],
            ]
        ];

        return Inertia::render('SubmitViews', $viewsData);
    }

    public function store(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string',
            'views_count' => 'required|integer|min:1',
            'screenshot' => 'required|file|image|max:2048',
        ]);

        // Process the submission here
        // You can store the file, create database records, etc.

        return back()->with('success', 'Views submitted successfully!');
    }
}