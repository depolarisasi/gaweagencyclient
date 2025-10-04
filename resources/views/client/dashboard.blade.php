@extends('layouts.app')

@section('title', 'Client Dashboard - Gawe Agency')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-xl border-r border-gray-200">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Client Portal</h3>
                        <p class="text-xs text-gray-500">Your Projects Hub</p>
                    </div>
                </div>
                
                <nav class="space-y-2">
                    <a href="{{ route('client.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 bg-green-50 text-green-700 rounded-lg border border-green-200">
                        <i class="fas fa-home w-5"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">My Services</p>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-project-diagram w-5"></i>
                            <span>My Projects</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-shopping-cart w-5"></i>
                            <span>My Orders</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-file-invoice w-5"></i>
                            <span>Invoices & Billing</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Explore</p>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-store w-5"></i>
                            <span>Browse Services</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Support</p>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-life-ring w-5"></i>
                            <span>Get Help</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-comments w-5"></i>
                            <span>Support Tickets</span>
                        </a>
                    </div>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Welcome Back!</h1>
                    <p class="text-gray-600 mt-1">Track your projects and manage your services</p>
                </div>
                <div class="flex space-x-3">
                    <button class="btn btn-outline btn-sm">
                        <i class="fas fa-download mr-2"></i>Download Invoice
                    </button>
                    <button class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-2"></i>New Order
                    </button>
                </div>
            </div>
            
            <!-- Welcome Card -->
            <div class="bg-gradient-to-r from-green-500 to-blue-600 rounded-xl p-6 text-white mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold mb-2">Hello, {{ auth()->user()->name }}!</h2>
                        <p class="text-green-100">
                            @if(auth()->user()->company)
                                Welcome back to your {{ auth()->user()->company }} project dashboard. 
                            @endif
                            Let's see how your projects are progressing.
                        </p>
                        <div class="mt-4">
                            <button class="bg-white text-green-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                                <i class="fas fa-rocket mr-2"></i>Start New Project
                            </button>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <i class="fas fa-user-circle text-6xl text-green-200 opacity-50"></i>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Active Projects</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Project::where('user_id', auth()->id())->where('status', 'active')->count() }}</p>
                            <p class="text-sm text-blue-600 mt-1">
                                <i class="fas fa-clock mr-1"></i>In progress
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-project-diagram text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Completed Projects</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Project::where('user_id', auth()->id())->where('status', 'completed')->count() }}</p>
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-check-circle mr-1"></i>Delivered
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Pending Invoices</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Invoice::where('user_id', auth()->id())->whereIn('status', ['sent', 'draft'])->count() }}</p>
                            <p class="text-sm text-orange-600 mt-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Needs payment
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-invoice text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Support Tickets</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\SupportTicket::where('user_id', auth()->id())->where('status', 'open')->count() }}</p>
                            <p class="text-sm text-purple-600 mt-1">
                                <i class="fas fa-headset mr-1"></i>Open tickets
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-life-ring text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- My Projects -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">My Projects</h3>
                        <a href="#" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</a>
                    </div>
                    <div class="space-y-4">
                        @forelse(\App\Models\Project::where('user_id', auth()->id())->latest()->take(5)->get() as $project)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-globe text-blue-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $project->project_name ?? 'Website Project #' . $project->id }}</h4>
                                            <p class="text-sm text-gray-500">Started {{ $project->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="mt-3">
                                        <div class="flex items-center justify-between text-sm mb-1">
                                            <span class="text-gray-600">Progress</span>
                                            <span class="font-medium">{{ $project->progress_percentage ?? 0 }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $project->progress_percentage ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-3">
                                    @if($project->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                    @elseif($project->status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Active</span>
                                    @elseif($project->status === 'completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($project->status) }}</span>
                                    @endif
                                    
                                    <button class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <i class="fas fa-project-diagram text-gray-300 text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No projects yet</h3>
                            <p class="text-gray-500 mb-4">Start your first project with us!</p>
                            <button class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Create Project
                            </button>
                        </div>
                        @endforelse
                    </div>
                </div>
                
                <!-- Quick Actions Sidebar -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button class="w-full flex items-center space-x-3 p-3 text-left hover:bg-green-50 rounded-lg transition-colors border border-green-200">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-plus text-green-600"></i>
                            </div>
                            <span class="font-medium text-gray-700">New Project</span>
                        </button>
                        
                        <button class="w-full flex items-center space-x-3 p-3 text-left hover:bg-blue-50 rounded-lg transition-colors">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-invoice text-blue-600"></i>
                            </div>
                            <span class="font-medium text-gray-700">View Invoices</span>
                        </button>
                        
                        <button class="w-full flex items-center space-x-3 p-3 text-left hover:bg-purple-50 rounded-lg transition-colors">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-headset text-purple-600"></i>
                            </div>
                            <span class="font-medium text-gray-700">Get Support</span>
                        </button>
                        
                        <button class="w-full flex items-center space-x-3 p-3 text-left hover:bg-orange-50 rounded-lg transition-colors">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-download text-orange-600"></i>
                            </div>
                            <span class="font-medium text-gray-700">Download Files</span>
                        </button>
                    </div>
                    
                    <!-- Support Card -->
                    <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-200">
                        <div class="flex items-center space-x-3 mb-3">
                            <i class="fas fa-life-ring text-blue-600"></i>
                            <h4 class="font-medium text-gray-900">Need Help?</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">Our support team is here to help you 24/7</p>
                        <button class="btn btn-sm btn-primary w-full">
                            <i class="fas fa-comments mr-2"></i>Start Chat
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection