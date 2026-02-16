<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontendUIController extends Controller
{
    // ===========================================
    // Home Page
    // ===========================================
    public function index()
    {
        return view('frontend.index');
    }

    // ===========================================
    // Product Pages
    // ===========================================
    public function productTasks()
    {
        return view('frontend.product.tasks');
    }

    public function productGantt()
    {
        return view('frontend.product.gantt');
    }

    public function productKanban()
    {
        return view('frontend.product.kanban');
    }

    public function productAttendance()
    {
        return view('frontend.product.attendance');
    }

    public function productLeave()
    {
        return view('frontend.product.leave');
    }

    public function productPerformance()
    {
        return view('frontend.product.performance');
    }

    public function productReports()
    {
        return view('frontend.product.reports');
    }

    public function productDashboard()
    {
        return view('frontend.product.dashboard');
    }

    public function productAnalytics()
    {
        return view('frontend.product.analytics');
    }

    // ===========================================
    // Solutions Pages
    // ===========================================
    public function solutionsEnterprise()
    {
        return view('frontend.solutions.enterprise');
    }

    public function solutionsStartups()
    {
        return view('frontend.solutions.startups');
    }

    public function solutionsHr()
    {
        return view('frontend.solutions.hr');
    }

    public function solutionsDevelopers()
    {
        return view('frontend.solutions.developers');
    }

    public function solutionsRemote()
    {
        return view('frontend.solutions.remote');
    }

    // ===========================================
    // Features & Pricing
    // ===========================================
    public function features()
    {
        return view('frontend.features');
    }

    public function pricing()
    {
        return view('frontend.pricing');
    }

    // ===========================================
    // Resources Pages
    // ===========================================
    public function blog()
    {
        return view('frontend.resources.blog');
    }

    public function blogSingle($slug)
    {
        return view('frontend.resources.blog-single', compact('slug'));
    }

    public function documentation()
    {
        return view('frontend.resources.documentation');
    }

    public function api()
    {
        return view('frontend.resources.api');
    }

    public function helpCenter()
    {
        return view('frontend.resources.help');
    }

    public function faq()
    {
        return view('frontend.resources.faq');
    }

    // ===========================================
    // Company Pages
    // ===========================================
    public function about()
    {
        return view('frontend.company.about');
    }

    public function careers()
    {
        return view('frontend.company.careers');
    }

    public function contact()
    {
        return view('frontend.company.contact');
    }

    public function contactSubmit(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        // Here you can add code to send email or save to database
        // For now, we'll just redirect with success message

        return redirect()->back()->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }

    public function privacy()
    {
        return view('frontend.company.privacy');
    }

    public function terms()
    {
        return view('frontend.company.terms');
    }
}
