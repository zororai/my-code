# Syllabus Creation Guide

## Overview

The Syllabus Management System at `http://student_portal_roshs.test/teacher/syllabus` allows teachers to create, manage, and organize curriculum topics for their subjects. This system serves as the foundation for schemes of work, assessments, and performance tracking.

---

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Database Structure](#database-structure)
3. [How Syllabus Topics Are Created](#how-syllabus-topics-are-created)
4. [Routes](#routes)
5. [Controller Logic](#controller-logic)
6. [User Interface](#user-interface)
7. [Validation Rules](#validation-rules)
8. [Integration with Other Systems](#integration-with-other-systems)
9. [Workflow Example](#workflow-example)

---

## System Architecture

### Components

- **Model**: `App\SyllabusTopic`
- **Controller**: `App\Http\Controllers\TeacherSyllabusController`
- **Views**: `resources/views/backend/teacher/syllabus/`
  - `index.blade.php` - List all topics
  - `create.blade.php` - Create new topics
  - `edit.blade.php` - Edit existing topic
- **Routes**: Defined in `routes/web.php` under Teacher middleware group

---

## Database Structure

### Table: `syllabus_topics`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Primary key |
| `subject_id` | bigint (FK) | References `subjects.id` |
| `name` | string | Topic name (e.g., "Quadratic Equations") |
| `description` | text (nullable) | Brief description of the topic |
| `learning_objectives` | text (nullable) | What students should learn |
| `suggested_periods` | integer | Number of teaching periods (1-20) |
| `order_index` | integer | Display order within subject/term |
| `term` | string (nullable) | "Term 1", "Term 2", or "Term 3" |
| `difficulty_level` | string | "easy", "medium", or "hard" |
| `is_active` | boolean | Whether topic is currently active |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

### Relationships

```php
// SyllabusTopic Model Relationships
public function subject()
{
    return $this->belongsTo(Subject::class);
}

public function assessments()
{
    return $this->hasMany(Assessment::class, 'syllabus_topic_id');
}

public function schemeTopics()
{
    return $this->hasMany(SchemeTopic::class, 'syllabus_topic_id');
}

public function performanceSnapshots()
{
    return $this->hasMany(TopicPerformanceSnapshot::class, 'syllabus_topic_id');
}

public function remedialLessons()
{
    return $this->hasMany(RemedialLesson::class, 'syllabus_topic_id');
}
```

### Indexes

- `subject_id, term` - Composite index for efficient querying by subject and term
- Foreign key on `subject_id` with CASCADE delete

---

## How Syllabus Topics Are Created

### Step-by-Step Process

#### 1. **Access the Creation Page**

Teachers navigate to `/teacher/syllabus` and click "Add New Topic" button, which routes to `/teacher/syllabus/create`.

#### 2. **Subject Authorization Check**

```php
$teacher = auth()->user()->teacher;
$subjects = $teacher->subjects; // Only subjects assigned to this teacher
```

Teachers can **only create topics for subjects they teach**. This is enforced at multiple levels:
- View level: Only assigned subjects appear in dropdown
- Controller level: Validation checks subject ownership

#### 3. **Form Submission**

The system supports **two creation modes**:

##### A. Single Topic Creation (Legacy)
```php
POST /teacher/syllabus
{
    "subject_id": 5,
    "name": "Quadratic Equations",
    "description": "Solving quadratic equations using various methods",
    "learning_objectives": "Students will be able to solve quadratic equations...",
    "term": "Term 1",
    "difficulty_level": "medium",
    "suggested_periods": 6,
    "order_index": 1,
    "is_active": true
}
```

##### B. Multiple Topics Creation (Current)
```php
POST /teacher/syllabus
{
    "multiple": "1",
    "subject_id": 5,
    "term": "Term 1",
    "topics": [
        {
            "name": "Quadratic Equations",
            "description": "Solving quadratic equations",
            "learning_objectives": "Students will solve...",
            "difficulty_level": "medium",
            "suggested_periods": 6,
            "order_index": 1,
            "is_active": true
        },
        {
            "name": "Simultaneous Equations",
            "description": "Solving two equations together",
            "learning_objectives": "Students will solve...",
            "difficulty_level": "hard",
            "suggested_periods": 8,
            "order_index": 2,
            "is_active": true
        }
    ]
}
```

#### 4. **Validation**

**For Multiple Topics:**
```php
$request->validate([
    'subject_id' => 'required|exists:subjects,id',
    'term' => 'required|in:Term 1,Term 2,Term 3',
    'topics' => 'required|array|min:1',
    'topics.*.name' => 'required|string|max:255',
    'topics.*.description' => 'nullable|string',
    'topics.*.learning_objectives' => 'nullable|string',
    'topics.*.difficulty_level' => 'required|in:easy,medium,hard',
    'topics.*.suggested_periods' => 'required|integer|min:1|max:20',
    'topics.*.order_index' => 'nullable|integer|min:0',
]);
```

**Authorization Check:**
```php
if (!$subjectIds->contains($request->subject_id)) {
    return back()->withErrors([
        'subject_id' => 'You can only create topics for subjects you teach.'
    ]);
}
```

#### 5. **Database Insertion**

```php
foreach ($request->topics as $topicData) {
    SyllabusTopic::create([
        'subject_id' => $request->subject_id,
        'term' => $request->term,
        'name' => $topicData['name'],
        'description' => $topicData['description'] ?? null,
        'learning_objectives' => $topicData['learning_objectives'] ?? null,
        'difficulty_level' => $topicData['difficulty_level'],
        'suggested_periods' => $topicData['suggested_periods'],
        'order_index' => $topicData['order_index'] ?? 0,
        'is_active' => isset($topicData['is_active']) ? true : false,
    ]);
    $createdCount++;
}
```

#### 6. **Success Response**

```php
return redirect()->route('teacher.syllabus.index')
    ->with('success', "{$createdCount} syllabus topic(s) created successfully!");
```

---

## Routes

### Teacher Routes (Middleware: `auth`, `role:Teacher`)

```php
Route::get('/teacher/syllabus', 'TeacherSyllabusController@index')
    ->name('teacher.syllabus.index');

Route::get('/teacher/syllabus/create', 'TeacherSyllabusController@create')
    ->name('teacher.syllabus.create');

Route::post('/teacher/syllabus', 'TeacherSyllabusController@store')
    ->name('teacher.syllabus.store');

Route::get('/teacher/syllabus/{id}/edit', 'TeacherSyllabusController@edit')
    ->name('teacher.syllabus.edit');

Route::put('/teacher/syllabus/{id}', 'TeacherSyllabusController@update')
    ->name('teacher.syllabus.update');

Route::delete('/teacher/syllabus/{id}', 'TeacherSyllabusController@destroy')
    ->name('teacher.syllabus.destroy');
```

### Admin Routes (Middleware: `auth`, `role:Admin`)

```php
Route::get('admin/syllabus', 'AdminSyllabusController@index')
    ->name('admin.syllabus.index');

Route::get('admin/syllabus/create', 'AdminSyllabusController@create')
    ->name('admin.syllabus.create');

Route::post('admin/syllabus', 'AdminSyllabusController@store')
    ->name('admin.syllabus.store');

Route::get('admin/syllabus/{id}/edit', 'AdminSyllabusController@edit')
    ->name('admin.syllabus.edit');

Route::put('admin/syllabus/{id}', 'AdminSyllabusController@update')
    ->name('admin.syllabus.update');

Route::delete('admin/syllabus/{id}', 'AdminSyllabusController@destroy')
    ->name('admin.syllabus.destroy');
```

---

## Controller Logic

### TeacherSyllabusController Methods

#### `index()` - List Topics
```php
public function index()
{
    $teacher = auth()->user()->teacher;
    $subjectIds = $teacher->subjects->pluck('id');
    
    $topics = SyllabusTopic::whereIn('subject_id', $subjectIds)
        ->with('subject')
        ->orderBy('subject_id')
        ->orderBy('order_index')
        ->paginate(20);
    
    $subjects = $teacher->subjects;
    
    return view('backend.teacher.syllabus.index', compact('topics', 'subjects'));
}
```

#### `create()` - Show Creation Form
```php
public function create()
{
    $teacher = auth()->user()->teacher;
    $subjects = $teacher->subjects;
    $terms = ['Term 1', 'Term 2', 'Term 3'];
    $difficultyLevels = ['easy', 'medium', 'hard'];
    
    return view('backend.teacher.syllabus.create', 
        compact('subjects', 'terms', 'difficultyLevels'));
}
```

#### `store()` - Save New Topics
Handles both single and multiple topic creation with validation and authorization checks.

#### `edit($id)` - Show Edit Form
```php
public function edit($id)
{
    $teacher = auth()->user()->teacher;
    $topic = SyllabusTopic::findOrFail($id);
    
    // Verify teacher teaches this subject
    $subjectIds = $teacher->subjects->pluck('id');
    if (!$subjectIds->contains($topic->subject_id)) {
        return redirect()->route('teacher.syllabus.index')
            ->with('error', 'You can only edit topics for subjects you teach.');
    }
    
    // ... return edit view
}
```

#### `update($id)` - Update Topic
Similar to edit, with validation and authorization.

#### `destroy($id)` - Delete Topic
```php
public function destroy($id)
{
    $teacher = auth()->user()->teacher;
    $topic = SyllabusTopic::findOrFail($id);
    
    // Authorization check
    $subjectIds = $teacher->subjects->pluck('id');
    if (!$subjectIds->contains($topic->subject_id)) {
        return redirect()->route('teacher.syllabus.index')
            ->with('error', 'You can only delete topics for subjects you teach.');
    }
    
    $topic->delete();
    
    return redirect()->route('teacher.syllabus.index')
        ->with('success', 'Syllabus topic deleted successfully!');
}
```

---

## User Interface

### Create Page (`create.blade.php`)

The UI uses **Alpine.js** for dynamic form management:

```javascript
function syllabusForm() {
    return {
        subjectId: '',
        term: 'Term 1',
        topics: [
            {
                name: '',
                description: '',
                learning_objectives: '',
                difficulty_level: 'medium',
                suggested_periods: 4,
                order_index: 1,
                is_active: true
            }
        ],
        addTopic() {
            this.topics.push({
                name: '',
                description: '',
                learning_objectives: '',
                difficulty_level: 'medium',
                suggested_periods: 4,
                order_index: this.topics.length + 1,
                is_active: true
            });
        },
        removeTopic(index) {
            if (this.topics.length > 1) {
                this.topics.splice(index, 1);
                // Update order indices
                this.topics.forEach((t, i) => t.order_index = i + 1);
            }
        }
    }
}
```

### Features

1. **Subject & Term Selection** - Dropdown for subject and term
2. **Dynamic Topic Addition** - Add/remove topics dynamically
3. **Per-Topic Fields**:
   - Topic Name (required)
   - Description (optional)
   - Learning Objectives (optional)
   - Difficulty Level (easy/medium/hard)
   - Suggested Periods (1-20)
   - Order Index (for sequencing)
   - Active Status (checkbox)
4. **Real-time Counter** - Shows number of topics being created
5. **Validation Feedback** - Displays errors if validation fails

### Index Page (`index.blade.php`)

Displays topics in a table with:
- Subject code and name
- Topic name and description
- Term
- Difficulty level (color-coded badges)
- Suggested periods
- Active/Inactive status
- Edit and Delete actions

---

## Validation Rules

### Required Fields
- `subject_id` - Must exist in subjects table
- `name` - String, max 255 characters
- `term` - Must be "Term 1", "Term 2", or "Term 3"
- `difficulty_level` - Must be "easy", "medium", or "hard"
- `suggested_periods` - Integer between 1 and 20

### Optional Fields
- `description` - Text
- `learning_objectives` - Text
- `order_index` - Integer, min 0
- `is_active` - Boolean (defaults to false if not checked)

### Authorization
- Teacher must be assigned to the subject
- Checked during create, update, and delete operations

---

## Integration with Other Systems

### 1. **Schemes of Work**
Syllabus topics are used to populate schemes of work:
```php
// In SchemeController
Route::get('/teacher/schemes/syllabus-topics', 'SchemeController@getSyllabusTopics')
    ->name('teacher.schemes.syllabus-topics');
```

Teachers select syllabus topics when creating weekly scheme entries.

### 2. **Assessments**
Assessments can be linked to syllabus topics:
```php
// In Assessment model
public function syllabusTopic()
{
    return $this->belongsTo(SyllabusTopic::class, 'syllabus_topic_id');
}
```

### 3. **Performance Tracking**
Topic performance snapshots track student mastery:
```php
// TopicPerformanceSnapshot
$table->foreign('syllabus_topic_id')
    ->references('id')->on('syllabus_topics')
    ->onDelete('cascade');
```

### 4. **Remedial Lessons**
Remedial lessons are created for topics where students struggle:
```php
// RemedialLesson
$table->foreign('syllabus_topic_id')
    ->references('id')->on('syllabus_topics')
    ->onDelete('cascade');
```

---

## Workflow Example

### Scenario: Math Teacher Creating Term 1 Topics

1. **Login** as Teacher
2. **Navigate** to `/teacher/syllabus`
3. **Click** "Add New Topic" button
4. **Select** Subject: "MATH001 - Mathematics"
5. **Select** Term: "Term 1"
6. **Add First Topic**:
   - Name: "Linear Equations"
   - Description: "Solving linear equations in one variable"
   - Difficulty: Easy
   - Suggested Periods: 4
   - Order: 1
   - Active: ✓
7. **Click** "Add Topic" to add more
8. **Add Second Topic**:
   - Name: "Quadratic Equations"
   - Description: "Solving quadratic equations using factorization"
   - Difficulty: Medium
   - Suggested Periods: 6
   - Order: 2
   - Active: ✓
9. **Submit** form
10. **System**:
    - Validates all fields
    - Checks teacher teaches Mathematics
    - Creates 2 syllabus topics in database
    - Redirects to index with success message: "2 syllabus topic(s) created successfully!"

### Database Result

```sql
INSERT INTO syllabus_topics VALUES
(1, 5, 'Linear Equations', 'Solving linear equations...', NULL, 4, 1, 'Term 1', 'easy', 1, NOW(), NOW()),
(2, 5, 'Quadratic Equations', 'Solving quadratic equations...', NULL, 6, 2, 'Term 1', 'medium', 1, NOW(), NOW());
```

---

## Model Scopes

The `SyllabusTopic` model provides convenient query scopes:

```php
// Get only active topics
$activeTopics = SyllabusTopic::active()->get();

// Get topics by term
$term1Topics = SyllabusTopic::byTerm('Term 1')->get();

// Get topics by subject
$mathTopics = SyllabusTopic::bySubject(5)->get();

// Get ordered topics
$orderedTopics = SyllabusTopic::ordered()->get();

// Combine scopes
$topics = SyllabusTopic::active()
    ->bySubject(5)
    ->byTerm('Term 1')
    ->ordered()
    ->get();
```

---

## Security Features

1. **Authentication Required** - Only logged-in users can access
2. **Role-Based Access** - Only Teachers and Admins can manage syllabus
3. **Subject Authorization** - Teachers can only manage topics for their subjects
4. **CSRF Protection** - All forms include `@csrf` token
5. **Mass Assignment Protection** - Only fillable fields can be set
6. **SQL Injection Prevention** - Eloquent ORM with parameter binding
7. **XSS Prevention** - Blade templating escapes output by default

---

## Best Practices

1. **Logical Ordering** - Use `order_index` to sequence topics logically
2. **Clear Naming** - Use descriptive topic names
3. **Learning Objectives** - Define clear, measurable objectives
4. **Realistic Periods** - Estimate teaching periods accurately
5. **Difficulty Progression** - Start with easy topics, progress to hard
6. **Active Management** - Mark topics inactive when not in use
7. **Term Planning** - Distribute topics evenly across terms

---

## Troubleshooting

### Common Issues

**Problem**: "You can only create topics for subjects you teach"
- **Cause**: Teacher not assigned to the selected subject
- **Solution**: Admin must assign teacher to subject first

**Problem**: Topics not appearing in schemes
- **Cause**: Topics marked as inactive
- **Solution**: Edit topic and check "Active" checkbox

**Problem**: Cannot delete topic
- **Cause**: Topic has related assessments or scheme entries
- **Solution**: Delete or reassign related records first, or use soft delete

---

## Future Enhancements

- Bulk import from CSV/Excel
- Topic templates for common subjects
- Topic dependencies (prerequisites)
- Resource attachments (PDFs, videos)
- Collaborative topic creation
- Version history tracking
- Topic sharing between teachers

---

## Summary

The Syllabus Creation System provides a structured way for teachers to define curriculum topics that integrate with schemes of work, assessments, and performance tracking. The system enforces authorization, validates input, and maintains data integrity through foreign key constraints and proper relationships.

**Key Points:**
- Teachers create topics for subjects they teach
- Multiple topics can be created at once
- Topics are organized by subject, term, and order
- Topics integrate with schemes, assessments, and remedial systems
- Full CRUD operations with proper authorization checks
