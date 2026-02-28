# Timetable Creation Guide

## Overview

The timetable system is a sophisticated automated scheduling system that generates weekly timetables for classes based on configurable settings, subject requirements, and teacher availability. It handles multiple lesson types (single, double, triple, quad periods), special slots (assembly, sports, clubs), practicals, and automatically prevents scheduling conflicts.

## System Architecture

### Database Models

#### 1. **Timetable Model** (`app/Timetable.php`)
Stores individual timetable slots.

**Fields:**
- `class_id` - Reference to Grade/Class
- `subject_id` - Reference to Subject (nullable for breaks/special slots)
- `teacher_id` - Reference to Teacher (nullable)
- `day` - Day of week (Monday-Friday)
- `start_time` - Slot start time
- `end_time` - Slot end time
- `slot_type` - Type: 'subject', 'break', 'lunch', 'clubs', 'practical', 'special'
- `slot_name` - Name for special slots (e.g., "Assembly", "Sports")
- `slot_order` - Order of slot in the day
- `academic_year` - Academic year (e.g., "2024")
- `term` - Term number (1-3)

**Key Methods:**
- `checkTeacherConflict()` - Validates teacher availability across time slots

#### 2. **TimetableSetting Model** (`app/TimetableSetting.php`)
Stores configuration for timetable generation.

**Fields:**
- `class_id` - Reference to Grade/Class
- `start_time` - School day start time
- `break_start` - Morning break start time
- `break_end` - Morning break end time
- `lunch_start` - Lunch start time
- `lunch_end` - Lunch end time
- `end_time` - School day end time
- `subject_duration` - Duration of each period in minutes
- `academic_year` - Academic year
- `term` - Term number

#### 3. **Subject Model** (`app/Subject.php`)
Defines subjects and their lesson requirements.

**Key Fields:**
- `name` - Subject name
- `teacher_id` - Assigned teacher
- `single_lessons_per_week` - Number of single-period lessons
- `double_lessons_per_week` - Number of double-period lessons (2 consecutive periods)
- `triple_lessons_per_week` - Number of triple-period lessons (3 consecutive periods)
- `quad_lessons_per_week` - Number of quad-period lessons (4 consecutive periods)
- `is_practical` - Whether subject is a practical subject

#### 4. **Grade Model** (`app/Grade.php`)
Represents classes/grades.

**Relationships:**
- `subjects()` - Many-to-many relationship with subjects
- `teacher()` - Class teacher (form teacher)

## Timetable Generation Process

### Routes

```php
// View all timetables
GET /admin/timetable - AdminTimetableController@index

// Master timetable (all classes on one sheet)
GET /admin/timetable/master - AdminTimetableController@master

// Create new timetable
GET /admin/timetable/create - AdminTimetableController@create
POST /admin/timetable - AdminTimetableController@store

// View specific class timetable
GET /admin/timetable/{id} - AdminTimetableController@show

// Edit timetable
GET /admin/timetable/{id}/edit - AdminTimetableController@edit
PUT /admin/timetable/{id} - AdminTimetableController@update

// Delete timetable
DELETE /admin/timetable/{id} - AdminTimetableController@destroy

// Check for conflicts
POST /admin/timetable/check-conflicts - AdminTimetableController@checkConflicts

// Clear timetables
POST /admin/timetable/clear - AdminTimetableController@clear
```

### Step 1: Configuration (`create()` method)

The admin navigates to `/admin/timetable/create` and configures:

**Basic Settings:**
- Select one or more classes
- School day times (start, end)
- Break times (start, end)
- Lunch times (start, end)
- Subject duration (minutes per period)
- Academic year and term

**Special Slots (Optional):**
- **Before Break** - e.g., Assembly (name, days, number of periods)
- **After Break** - e.g., Sports (name, days, number of periods)
- **After Lunch** - e.g., Reading (name, days, number of periods)

**Clubs (Optional):**
- Days clubs run
- Position (after_lunch)
- Start and end times
- Number of periods

**Practicals (Optional):**
- Select practical subjects
- System automatically assigns:
  - 2-period practical on one day
  - 4-period practical on another day
  - Days are randomized per class to avoid teacher conflicts

### Step 2: Validation (`store()` method)

The system validates:
- At least one class selected
- Valid time formats (H:i)
- Subject duration between 20-120 minutes
- Academic year and term provided
- Special slot configurations (if enabled)

### Step 3: Settings Storage

For each selected class:
1. Create/update `TimetableSetting` record
2. Store base configuration
3. Prepare special slots configuration with randomized practical days

### Step 4: Timetable Generation (`generateTimetable()` method)

This is the core algorithm that creates the actual timetable.

#### Phase 1: Delete Existing Timetable
```php
Timetable::where('class_id', $settings->class_id)
    ->where('academic_year', $settings->academic_year)
    ->where('term', $settings->term)
    ->delete();
```

#### Phase 2: Build Lesson Pool

The system creates a pool of all lessons that need to be scheduled:

```php
// For each subject assigned to the class:
// - Quad lessons (4 consecutive periods)
// - Triple lessons (3 consecutive periods)
// - Double lessons (2 consecutive periods)
// - Single lessons (1 period)

// Example:
// Mathematics: 2 double lessons + 3 single lessons = 7 periods/week
// English: 1 triple lesson + 2 single lessons = 5 periods/week
```

**Important:** Practical subjects are excluded from the regular lesson pool and handled separately in the Clubs/Activities section.

#### Phase 3: Generate Day Structure (`generateDayStructure()` method)

For each day (Monday-Friday), the system creates a time structure:

**Fixed Elements (in order):**
1. **Start of day** → Subject slots
2. **Before Break Special Slot** (if configured)
3. **Break** (fixed time)
4. **After Break Special Slot** (if configured)
5. **Subject slots**
6. **Lunch** (fixed time)
7. **After Lunch Special Slot** (if configured)
8. **Practicals** (if configured for this day - standalone, separate from clubs)
9. **Clubs** (if configured for this day)
10. **Subject slots** (if time remains)
11. **End of day**

**Time Calculation:**
- Converts all times to minutes from midnight
- Creates slots based on `subject_duration`
- Ensures slots don't overlap with breaks/lunch
- Marks gap slots (too small to be useful) as free periods

**Practicals Handling:**
- Automatically scheduled after lunch
- 2 periods on one day, 4 periods on another
- Each practical subject gets its own slot at the same time
- All practical teachers share the same time slot
- Days are randomized per class to prevent teacher conflicts

#### Phase 4: Distribute Lessons (`generateTimetable()` continuation)

**Strict Constraints:**
1. **One Block Per Day Rule:** Each subject can appear only ONCE per day as a single consecutive block
   - A double lesson = 1 block (not 2 separate lessons)
   - A quad lesson = 1 block (not 4 separate lessons)
   - Subject cannot appear again on the same day, even non-consecutively

2. **Consecutive Periods:** Multi-period lessons must be consecutive
   - Can span across break/lunch
   - Cannot have other subjects in between

3. **Teacher Availability:** Teachers cannot be double-booked
   - Checks existing timetables for other classes
   - Checks current day structure for conflicts

**Distribution Algorithm:**

```php
// 1. Sort lesson pool: longer lessons first (harder to fit)
// 2. For each lesson in the pool:
//    a. Get available days (where subject not yet scheduled)
//    b. Shuffle days for variety
//    c. For each day:
//       - Check if subject already on this day → skip
//       - Find consecutive available slots
//       - Check teacher conflicts
//       - If found, place lesson and mark day as used
//       - Break and move to next lesson
//    d. If no placement found, lesson becomes free period
```

**Preferred Days Selection:**
- Only considers days where the subject hasn't been placed
- Shuffles available days for distribution variety
- Ensures subjects are spread across the week

**Consecutive Slot Finding (`findConsecutiveSlots()` method):**
1. Get all available subject slots (excluding gap slots)
2. Check if subject already exists anywhere on this day → return null if found
3. Find N consecutive available slots
4. Verify slots are truly consecutive (can span break/lunch)
5. Check teacher availability for entire time span
6. Return slot indices if valid, null otherwise

#### Phase 5: Create Database Records

For every slot in the day structure:
```php
Timetable::create([
    'class_id' => $settings->class_id,
    'subject_id' => $slot['subject_id'] ?? null,
    'teacher_id' => $slot['teacher_id'] ?? null,
    'day' => $day,
    'start_time' => $slot['start_time'],
    'end_time' => $slot['end_time'],
    'slot_type' => $slot['slot_type'],
    'slot_name' => $slot['slot_name'] ?? null,
    'slot_order' => $slotOrder,
    'academic_year' => $settings->academic_year,
    'term' => $settings->term,
]);
```

**Note:** Every slot is stored, including:
- Subject slots (with or without assigned subject)
- Breaks and lunch
- Special slots (assembly, sports, etc.)
- Clubs
- Practicals
- Free periods (unassigned subject slots)

### Step 5: Redirect

- **Single class:** Redirect to `admin.timetable.show` to view the generated timetable
- **Multiple classes:** Redirect to `admin.timetable.index` with success message

## Viewing Timetables

### Individual Class View (`show()` method)

Displays timetable for a specific class:
- Groups slots by day
- Shows subject, teacher, and time for each slot
- Displays breaks, lunch, and special slots
- Orders slots chronologically

### Master Timetable View (`master()` method)

Shows all classes on one sheet:
- Collects all unique time slots across all classes
- Groups timetables by class
- Displays in grid format for easy comparison
- Filters by academic year and term

## Editing Timetables

### Manual Editing (`edit()` and `update()` methods)

Admins can manually adjust timetables:

**Validation Checks:**
1. **Subject Assignment:** Subject must be assigned to the class
2. **Teacher Assignment:** Teacher must teach the subject
3. **Time Conflicts:** Teacher cannot be double-booked

**Conflict Handling:**
- Conflicts are collected and reported
- Conflicting slots are not updated
- Valid slots are updated successfully

### Conflict Detection

The `checkTeacherConflict()` method prevents double-booking:

```php
// Checks for time overlap:
// 1. New slot starts during existing slot
// 2. New slot ends during existing slot
// 3. New slot completely contains existing slot
// 4. Existing slot completely contains new slot
```

## Deletion and Clearing

### Delete Single Class (`destroy()` method)
- Deletes all timetable records for the class
- Deletes timetable settings for the class

### Clear by Term/Year (`clear()` method)
- Clear all classes for a specific term and year
- Clear specific class for a term and year
- Deletes both timetable records and settings

## Key Features

### 1. Multi-Period Lessons
- Supports 1, 2, 3, or 4 consecutive periods
- Automatically finds consecutive slots
- Can span across break/lunch times

### 2. Practical Subjects
- Scheduled separately in dedicated practical slots
- 2 periods on one day, 4 periods on another
- Randomized days per class to avoid teacher conflicts
- All practical teachers share the same time slot

### 3. Special Slots
- Assembly, sports, reading, etc.
- Configurable per day
- Multiple periods supported
- Fixed positions (before break, after break, after lunch)

### 4. Clubs and Activities
- Configurable days and times
- Multiple periods supported
- Separate from practicals

### 5. Teacher Conflict Prevention
- Checks across all classes
- Validates during generation
- Validates during manual editing

### 6. Free Periods
- Automatically created for unassigned slots
- Maintains day structure alignment
- Gap slots (too small) remain as free periods

### 7. Flexible Configuration
- Per-class settings
- Per-term settings
- Customizable school day times
- Adjustable period duration

## Best Practices

### When Creating Timetables:

1. **Configure Subject Lessons First:**
   - Set single/double/triple/quad lessons per week for each subject
   - Assign teachers to subjects
   - Mark practical subjects appropriately

2. **Set Realistic Times:**
   - Ensure break and lunch times allow for subject slots
   - Period duration should divide evenly into available time
   - Leave buffer time for transitions

3. **Use Special Slots Wisely:**
   - Don't over-schedule special slots
   - Consider student fatigue
   - Balance academic and non-academic time

4. **Handle Practicals Carefully:**
   - Select only true practical subjects
   - System will automatically schedule 2+4 periods
   - Different classes get different days to avoid conflicts

5. **Generate Multiple Classes Together:**
   - Helps prevent teacher conflicts
   - System randomizes practical days per class
   - More efficient than one-by-one

### When Editing Timetables:

1. **Check Conflicts First:**
   - Use the conflict checker before saving
   - Verify teacher availability
   - Ensure subject is assigned to class

2. **Maintain Balance:**
   - Don't overload certain days
   - Spread subjects across the week
   - Keep multi-period lessons intact

3. **Consider Teacher Workload:**
   - Check teacher's full schedule across classes
   - Avoid back-to-back classes in different locations
   - Allow preparation time

## Technical Notes

### Time Format
- All times stored as `H:i:s` (e.g., "08:00:00")
- Input accepts `H:i` format
- Converted to minutes for calculations

### Slot Order
- Zero-indexed
- Maintains chronological order
- Used for sorting and display

### Academic Year and Term
- Academic year: string (e.g., "2024")
- Term: integer 1-3
- Required for all operations

### Practical Day Combinations
```php
$practicalDayCombinations = [
    ['day_2periods' => 'Monday', 'day_4periods' => 'Wednesday'],
    ['day_2periods' => 'Tuesday', 'day_4periods' => 'Thursday'],
    ['day_2periods' => 'Monday', 'day_4periods' => 'Thursday'],
    ['day_2periods' => 'Tuesday', 'day_4periods' => 'Friday'],
    ['day_2periods' => 'Wednesday', 'day_4periods' => 'Friday'],
    ['day_2periods' => 'Monday', 'day_4periods' => 'Friday'],
    ['day_2periods' => 'Tuesday', 'day_4periods' => 'Wednesday'],
    ['day_2periods' => 'Wednesday', 'day_4periods' => 'Thursday'],
];
```
Classes cycle through these combinations to distribute practical times.

## Troubleshooting

### Issue: Lessons Not Placed
**Cause:** Not enough available slots or too many constraints
**Solution:** 
- Reduce number of lessons per week
- Increase school day duration
- Reduce special slots
- Check for teacher conflicts

### Issue: Teacher Conflicts
**Cause:** Teacher assigned to multiple classes at same time
**Solution:**
- Regenerate timetables for all affected classes together
- Manually adjust conflicting slots
- Assign different teachers

### Issue: Unbalanced Days
**Cause:** Random distribution or constraint conflicts
**Solution:**
- Regenerate timetable (uses shuffling for variety)
- Manually redistribute lessons
- Adjust lesson type configuration

### Issue: Practical Subjects in Regular Slots
**Cause:** Practical subjects not properly configured
**Solution:**
- Ensure subjects are marked as practical
- Include in practical_subjects array when generating
- Regenerate timetable

## Summary

The timetable generation system is a sophisticated automated scheduler that:
- Generates complete weekly timetables for multiple classes
- Handles various lesson types (single to quad periods)
- Manages special slots, clubs, and practicals
- Prevents scheduling conflicts automatically
- Allows manual adjustments with validation
- Maintains data integrity across academic years and terms

The system prioritizes constraint satisfaction (one block per day, consecutive periods, teacher availability) while attempting to distribute lessons evenly across the week.
