<?php
/**
 * Base class for question import and export formats.
 *
 * @author Martin Dougiamas, Howard Miller, and many others.
 *         {@link http://moodle.org}
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questionbank
 * @subpackage importexport
 */
class qformat_default {

    public $displayerrors = true;
    public $category = NULL;
    public $questions = array();
    public $course = NULL;
    public $filename = '';
    public $realfilename = '';
    public $matchgrades = 'error';
    public $catfromfile = 0;
    public $contextfromfile = 0;
    public $cattofile = 0;
    public $contexttofile = 0;
    public $questionids = array();
    public $importerrors = 0;
    public $stoponerror = true;
    public $translator = null;
    public $canaccessbackupdata = true;

    protected $importcontext = null;

// functions to indicate import/export functionality
// override to return true if implemented

    /** @return boolean whether this plugin provides import functionality. */
    function provide_import() {
        return false;
    }

    /** @return boolean whether this plugin provides export functionality. */
    function provide_export() {
        return false;
    }

    /** The string mime-type of the files that this plugin reads or writes. */
    function mime_type() {
        return mimeinfo('type', $this->export_file_extension());
    }

    /**
     * @return string the file extension (including .) that is normally used for
     * files handled by this plugin.
     */
    function export_file_extension() {
        return '.txt';
    }

// Accessor methods

    /**
     * set the category
     * @param object category the category object
     */
    function setCategory($category) {
        if (count($this->questions)) {
            debugging('You shouldn\'t call setCategory after setQuestions');
        }
        $this->category = $category;
    }

    /**
     * Set the specific questions to export. Should not include questions with
     * parents (sub questions of cloze question type).
     * Only used for question export.
     * @param array of question objects
     */
    function setQuestions($questions) {
        if ($this->category !== null) {
            debugging('You shouldn\'t call setQuestions after setCategory');
        }
        $this->questions = $questions;
    }

    /**
     * set the course class variable
     * @param course object Moodle course variable
     */
    function setCourse($course) {
        $this->course = $course;
    }

    /**
     * set an array of contexts.
     * @param array $contexts Moodle course variable
     */
    function setContexts($contexts) {
        $this->contexts = $contexts;
        $this->translator = new context_to_string_translator($this->contexts);
    }

    /**
     * set the filename
     * @param string filename name of file to import/export
     */
    function setFilename($filename) {
        $this->filename = $filename;
    }

    /**
     * set the "real" filename
     * (this is what the user typed, regardless of wha happened next)
     * @param string realfilename name of file as typed by user
     */
    function setRealfilename($realfilename) {
        $this->realfilename = $realfilename;
    }

    /**
     * set matchgrades
     * @param string matchgrades error or nearest for grades
     */
    function setMatchgrades($matchgrades) {
        $this->matchgrades = $matchgrades;
    }

    /**
     * set catfromfile
     * @param bool catfromfile allow categories embedded in import file
     */
    function setCatfromfile($catfromfile) {
        $this->catfromfile = $catfromfile;
    }

    /**
     * set contextfromfile
     * @param bool $contextfromfile allow contexts embedded in import file
     */
    function setContextfromfile($contextfromfile) {
        $this->contextfromfile = $contextfromfile;
    }

    /**
     * set cattofile
     * @param bool cattofile exports categories within export file
     */
    function setCattofile($cattofile) {
        $this->cattofile = $cattofile;
    }

    /**
     * set contexttofile
     * @param bool cattofile exports categories within export file
     */
    function setContexttofile($contexttofile) {
        $this->contexttofile = $contexttofile;
    }

    /**
     * set stoponerror
     * @param bool stoponerror stops database write if any errors reported
     */
    function setStoponerror($stoponerror) {
        $this->stoponerror = $stoponerror;
    }

    /**
     * @param boolean $canaccess Whether the current use can access the backup data folder. Determines
     * where export files are saved.
     */
    function set_can_access_backupdata($canaccess) {
        $this->canaccessbackupdata = $canaccess;
    }

/***********************
 * IMPORTING FUNCTIONS
 ***********************/

    /**
     * Handle parsing error
     */
    function error($message, $text='', $questionname='') {
        $importerrorquestion = get_string('importerrorquestion','quiz');

        echo "<div class=\"importerror\">\n";
        echo "<strong>$importerrorquestion $questionname</strong>";
        if (!empty($text)) {
            $text = s($text);
            echo "<blockquote>$text</blockquote>\n";
        }
        echo "<strong>$message</strong>\n";
        echo "</div>";

         $this->importerrors++;
    }

    /**
     * Import for questiontype plugins
     * Do not override.
     * @param data mixed The segment of data containing the question
     * @param question object processed (so far) by standard import code if appropriate
     * @param extra mixed any additional format specific data that may be passed by the format
     * @param qtypehint hint about a question type from format
     * @return object question object suitable for save_options() or false if cannot handle
     */
    function try_importing_using_qtypes($data, $question=null, $extra=null, $qtypehint='') {
        global $QTYPES;

        // work out what format we are using
        $formatname = substr(get_class($this), strlen('qformat_'));
        $methodname = "import_from_$formatname";

        //first try importing using a hint from format
        if (!empty($qtypehint)) {
            $qtype = $QTYPES[$qtypehint];
            if (is_object($qtype) && method_exists($qtype, $methodname)) {
                $question = $qtype->$methodname($data, $question, $this, $extra);
                if ($question) {
                    return $question;
                }
            }
        }

        // loop through installed questiontypes checking for
        // function to handle this question
        foreach ($QTYPES as $qtype) {
            if (method_exists($qtype, $methodname)) {
                if ($question = $qtype->$methodname($data, $question, $this, $extra)) {
                    return $question;
                }
            }
        }
        return false;
    }

    /**
     * Perform any required pre-processing
     * @return boolean success
     */
    function importpreprocess() {
        return true;
    }

    /**
     * Process the file
     * This method should not normally be overidden
     * @param object $context
     * @return boolean success
     */
    function importprocess($category) {
        global $USER, $CFG, $DB, $OUTPUT, $QTYPES;

        $context = $category->context;
        $this->importcontext = $context;

        // reset the timer in case file upload was slow
        set_time_limit(0);

        // STAGE 1: Parse the file
        echo $OUTPUT->notification(get_string('parsingquestions','quiz'));

        if (! $lines = $this->readdata($this->filename)) {
            echo $OUTPUT->notification(get_string('cannotread','quiz'));
            return false;
        }

        if (!$questions = $this->readquestions($lines, $context)) {   // Extract all the questions
            echo $OUTPUT->notification(get_string('noquestionsinfile','quiz'));
            return false;
        }

        // STAGE 2: Write data to database
        echo $OUTPUT->notification(get_string('importingquestions','quiz',$this->count_questions($questions)));

        // check for errors before we continue
        if ($this->stoponerror and ($this->importerrors>0)) {
            echo $OUTPUT->notification(get_string('importparseerror','quiz'));
            return true;
        }

        // get list of valid answer grades
        $grades = get_grade_options();
        $gradeoptionsfull = $grades->gradeoptionsfull;

        // check answer grades are valid
        // (now need to do this here because of 'stop on error': MDL-10689)
        $gradeerrors = 0;
        $goodquestions = array();
        foreach ($questions as $question) {

            if (!empty($question->fraction) and (is_array($question->fraction))) {
                $fractions = $question->fraction;
                $answersvalid = true; // in case they are!
                foreach ($fractions as $key => $fraction) {
                    $newfraction = match_grade_options($gradeoptionsfull, $fraction, $this->matchgrades);
                    if ($newfraction===false) {
                        $answersvalid = false;
                    }
                    else {
                        $fractions[$key] = $newfraction;
                    }
                }
                if (!$answersvalid) {
                    echo $OUTPUT->notification(get_string('matcherror', 'quiz'));
                    ++$gradeerrors;
                    continue;
                }
                else {
                    $question->fraction = $fractions;
                }
            }
            $goodquestions[] = $question;
        }
        $questions = $goodquestions;

        // check for errors before we continue
        if ($this->stoponerror and ($gradeerrors>0)) {
            return false;
        }

        // count number of questions processed
        $count = 0;

        foreach ($questions as $question) {   // Process and store each question

            // reset the php timeout
            @set_time_limit(0);

            // check for category modifiers
            if ($question->qtype == 'category') {
                if ($this->catfromfile) {
                    // find/create category object
                    $catpath = $question->category;
                    $newcategory = $this->create_category_path($catpath);
                    if (!empty($newcategory)) {
                        $this->category = $newcategory;
                    }
                }
                continue;
            }
            $question->context = $context;

            $count++;

            echo "<hr /><p><b>$count</b>. ".$this->format_question_text($question)."</p>";

            $question->category = $this->category->id;
            $question->stamp = make_unique_id_code();  // Set the unique code (not to be changed)

            $question->createdby = $USER->id;
            $question->timecreated = time();
            $question->modifiedby = $USER->id;
            $question->timemodified = time();

            $question->id = $DB->insert_record('question', $question);
            if (isset($question->questiontextfiles)) {
                foreach ($question->questiontextfiles as $file) {
                    $QTYPES[$question->qtype]->import_file($context, 'question', 'questiontext', $question->id, $file);
                }
            }
            if (isset($question->generalfeedbackfiles)) {
                foreach ($question->generalfeedbackfiles as $file) {
                    $QTYPES[$question->qtype]->import_file($context, 'question', 'generalfeedback', $question->id, $file);
                }
            }

            $this->questionids[] = $question->id;

            // Now to save all the answers and type-specific options

            $result = $QTYPES[$question->qtype]->save_question_options($question);

            if (!empty($CFG->usetags) && isset($question->tags)) {
                require_once($CFG->dirroot . '/tag/lib.php');
                tag_set('question', $question->id, $question->tags);
            }

            if (!empty($result->error)) {
                echo $OUTPUT->notification($result->error);
                return false;
            }

            if (!empty($result->notice)) {
                echo $OUTPUT->notification($result->notice);
                return true;
            }

            // Give the question a unique version stamp determined by question_hash()
            $DB->set_field('question', 'version', question_hash($question), array('id'=>$question->id));
        }
        return true;
    }

    /**
     * Count all non-category questions in the questions array.
     *
     * @param array questions An array of question objects.
     * @return int The count.
     *
     */
    function count_questions($questions) {
        $count = 0;
        if (!is_array($questions)) {
            return $count;
        }
        foreach ($questions as $question) {
            if (!is_object($question) || !isset($question->qtype) || ($question->qtype == 'category')) {
                continue;
            }
            $count++;
        }
        return $count;
    }

    /**
     * find and/or create the category described by a delimited list
     * e.g. $course$/tom/dick/harry or tom/dick/harry
     *
     * removes any context string no matter whether $getcontext is set
     * but if $getcontext is set then ignore the context and use selected category context.
     *
     * @param string catpath delimited category path
     * @param int courseid course to search for categories
     * @return mixed category object or null if fails
     */
    function create_category_path($catpath) {
        global $DB;
        $catnames = $this->split_category_path($catpath);
        $parent = 0;
        $category = null;

        // check for context id in path, it might not be there in pre 1.9 exports
        $matchcount = preg_match('/^\$([a-z]+)\$$/', $catnames[0], $matches);
        if ($matchcount==1) {
            $contextid = $this->translator->string_to_context($matches[1]);
            array_shift($catnames);
        } else {
            $contextid = false;
        }

        if ($this->contextfromfile && $contextid !== false) {
            $context = get_context_instance_by_id($contextid);
            require_capability('moodle/question:add', $context);
        } else {
            $context = get_context_instance_by_id($this->category->contextid);
        }

        // Now create any categories that need to be created.
        foreach ($catnames as $catname) {
            if ($category = $DB->get_record('question_categories', array('name' => $catname, 'contextid' => $context->id, 'parent' => $parent))) {
                $parent = $category->id;
            } else {
                require_capability('moodle/question:managecategory', $context);
                // create the new category
                $category = new stdClass();
                $category->contextid = $context->id;
                $category->name = $catname;
                $category->info = '';
                $category->parent = $parent;
                $category->sortorder = 999;
                $category->stamp = make_unique_id_code();
                $id = $DB->insert_record('question_categories', $category);
                $category->id = $id;
                $parent = $id;
            }
        }
        return $category;
    }

    /**
     * Return complete file within an array, one item per line
     * @param string filename name of file
     * @return mixed contents array or false on failure
     */
    function readdata($filename) {
        if (is_readable($filename)) {
            $filearray = file($filename);

            /// Check for Macintosh OS line returns (ie file on one line), and fix
            if (preg_match("~\r~", $filearray[0]) AND !preg_match("~\n~", $filearray[0])) {
                return explode("\r", $filearray[0]);
            } else {
                return $filearray;
            }
        }
        return false;
    }

    /**
     * Parses an array of lines into an array of questions,
     * where each item is a question object as defined by
     * readquestion().   Questions are defined as anything
     * between blank lines.
     *
     * If your format does not use blank lines as a delimiter
     * then you will need to override this method. Even then
     * try to use readquestion for each question
     * @param array lines array of lines from readdata
     * @param object $context
     * @return array array of question objects
     */
    function readquestions($lines, $context) {

        $questions = array();
        $currentquestion = array();

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                if (!empty($currentquestion)) {
                    if ($question = $this->readquestion($currentquestion)) {
                        $questions[] = $question;
                    }
                    $currentquestion = array();
                }
            } else {
                $currentquestion[] = $line;
            }
        }

        if (!empty($currentquestion)) {  // There may be a final question
            if ($question = $this->readquestion($currentquestion, $context)) {
                $questions[] = $question;
            }
        }

        return $questions;
    }

    /**
     * return an "empty" question
     * Somewhere to specify question parameters that are not handled
     * by import but are required db fields.
     * This should not be overridden.
     * @return object default question
     */
    function defaultquestion() {
        global $CFG;
        static $defaultshuffleanswers = null;
        if (is_null($defaultshuffleanswers)) {
            $defaultshuffleanswers = get_config('quiz', 'shuffleanswers');
        }

        $question = new stdClass();
        $question->shuffleanswers = $defaultshuffleanswers;
        $question->defaultgrade = 1;
        $question->image = "";
        $question->usecase = 0;
        $question->multiplier = array();
        $question->generalfeedback = '';
        $question->correctfeedback = '';
        $question->partiallycorrectfeedback = '';
        $question->incorrectfeedback = '';
        $question->answernumbering = 'abc';
        $question->penalty = 0.1;
        $question->length = 1;

        // this option in case the questiontypes class wants
        // to know where the data came from
        $question->export_process = true;
        $question->import_process = true;

        return $question;
    }

    /**
     * Given the data known to define a question in
     * this format, this function converts it into a question
     * object suitable for processing and insertion into Moodle.
     *
     * If your format does not use blank lines to delimit questions
     * (e.g. an XML format) you must override 'readquestions' too
     * @param $lines mixed data that represents question
     * @return object question object
     */
    function readquestion($lines) {

        $formatnotimplemented = get_string('formatnotimplemented','quiz');
        echo "<p>$formatnotimplemented</p>";

        return NULL;
    }

    /**
     * Override if any post-processing is required
     * @return boolean success
     */
    function importpostprocess() {
        return true;
    }


/*******************
 * EXPORT FUNCTIONS
 *******************/

    /**
     * Provide export functionality for plugin questiontypes
     * Do not override
     * @param name questiontype name
     * @param question object data to export
     * @param extra mixed any addition format specific data needed
     * @return string the data to append to export or false if error (or unhandled)
     */
    function try_exporting_using_qtypes($name, $question, $extra=null) {
        global $QTYPES;

        // work out the name of format in use
        $formatname = substr(get_class($this), strlen('qformat_'));
        $methodname = "export_to_$formatname";

        if (array_key_exists($name, $QTYPES)) {
            $qtype = $QTYPES[ $name ];
            if (method_exists($qtype, $methodname)) {
                if ($data = $qtype->$methodname($question, $this, $extra)) {
                    return $data;
                }
            }
        }
        return false;
    }

    /**
     * Do any pre-processing that may be required
     * @param boolean success
     */
    function exportpreprocess() {
        return true;
    }

    /**
     * Enable any processing to be done on the content
     * just prior to the file being saved
     * default is to do nothing
     * @param string output text
     * @param string processed output text
     */
    function presave_process($content) {
        return $content;
    }

    /**
     * Do the export
     * For most types this should not need to be overrided
     * @return stored_file
     */
    function exportprocess() {
        global $CFG, $OUTPUT, $DB, $USER;

        // get the questions (from database) in this category
        // only get q's with no parents (no cloze subquestions specifically)
        if ($this->category) {
            $questions = get_questions_category($this->category, true);
        } else {
            $questions = $this->questions;
        }

        //echo $OUTPUT->notification(get_string('exportingquestions','quiz'));
        $count = 0;

        // results are first written into string (and then to a file)
        // so create/initialize the string here
        $expout = "";

        // track which category questions are in
        // if it changes we will record the category change in the output
        // file if selected. 0 means that it will get printed before the 1st question
        $trackcategory = 0;

        $fs = get_file_storage();

        // iterate through questions
        foreach($questions as $question) {
            // used by file api
            $contextid = $DB->get_field('question_categories', 'contextid', array('id'=>$question->category));
            $question->contextid = $contextid;

            // do not export hidden questions
            if (!empty($question->hidden)) {
                continue;
            }

            // do not export random questions
            if ($question->qtype==RANDOM) {
                continue;
            }

            // check if we need to record category change
            if ($this->cattofile) {
                if ($question->category != $trackcategory) {
                    $trackcategory = $question->category;
                    $categoryname = $this->get_category_path($trackcategory, $this->contexttofile);

                    // create 'dummy' question for category export
                    $dummyquestion = new stdClass();
                    $dummyquestion->qtype = 'category';
                    $dummyquestion->category = $categoryname;
                    $dummyquestion->name = 'Switch category to ' . $categoryname;
                    $dummyquestion->id = 0;
                    $dummyquestion->questiontextformat = '';
                    $dummyquestion->contextid = 0;
                    $expout .= $this->writequestion($dummyquestion) . "\n";
                }
            }

            // export the question displaying message
            $count++;

            if (question_has_capability_on($question, 'view', $question->category)) {
                // files used by questiontext
                $files = $fs->get_area_files($contextid, 'question', 'questiontext', $question->id);
                $question->questiontextfiles = $files;
                // files used by generalfeedback
                $files = $fs->get_area_files($contextid, 'question', 'generalfeedback', $question->id);
                $question->generalfeedbackfiles = $files;
                if (!empty($question->options->answers)) {
                    foreach ($question->options->answers as $answer) {
                        $files = $fs->get_area_files($contextid, 'question', 'answerfeedback', $answer->id);
                        $answer->feedbackfiles = $files;
                    }
                }

                $expout .= $this->writequestion($question, $contextid) . "\n";
            }
        }

        // continue path for following error checks
        $course = $this->course;
        $continuepath = "$CFG->wwwroot/question/export.php?courseid=$course->id";

        // did we actually process anything
        if ($count==0) {
            print_error('noquestions','quiz',$continuepath);
        }

        // final pre-process on exported data
        $expout = $this->presave_process($expout);
        return $expout;
    }

    /**
     * get the category as a path (e.g., tom/dick/harry)
     * @param int id the id of the most nested catgory
     * @return string the path
     */
    function get_category_path($id, $includecontext = true) {
        global $DB;

        if (!$category = $DB->get_record('question_categories',array('id' =>$id))) {
            print_error('cannotfindcategory', 'error', '', $id);
        }
        $contextstring = $this->translator->context_to_string($category->contextid);

        $pathsections = array();
        do {
            $pathsections[] = $category->name;
            $id = $category->parent;
        } while ($category = $DB->get_record('question_categories', array('id' => $id)));

        if ($includecontext) {
            $pathsections[] = '$' . $contextstring . '$';
        }

        $path = $this->assemble_category_path(array_reverse($pathsections));

        return $path;
    }

    /**
     * Convert a list of category names, possibly preceeded by one of the
     * context tokens like $course$, into a string representation of the
     * category path.
     *
     * Names are separated by / delimiters. And /s in the name are replaced by //.
     *
     * To reverse the process and split the paths into names, use
     * {@link split_category_path()}.
     *
     * @param array $names
     * @return string
     */
    protected function assemble_category_path($names) {
        $escapednames = array();
        foreach ($names as $name) {
            $escapedname = str_replace('/', '//', $name);
            if (substr($escapedname, 0, 1) == '/') {
                $escapedname = ' ' . $escapedname;
            }
            if (substr($escapedname, -1) == '/') {
                $escapedname = $escapedname . ' ';
            }
            $escapednames[] = $escapedname;
        }
        return implode('/', $escapednames);
    }

    /**
     * Convert a string, as returned by {@link assemble_category_path()},
     * back into an array of category names.
     *
     * Each category name is cleaned by a call to clean_param(, PARAM_MULTILANG),
     * which matches the cleaning in question/category_form.php.
     *
     * @param string $path
     * @return array of category names.
     */
    protected function split_category_path($path) {
        $rawnames = preg_split('~(?<!/)/(?!/)~', $path);
        $names = array();
        foreach ($rawnames as $rawname) {
            $names[] = clean_param(trim(str_replace('//', '/', $rawname)), PARAM_MULTILANG);
        }
        return $names;
    }

    /**
     * Do an post-processing that may be required
     * @return boolean success
     */
    function exportpostprocess() {
        return true;
    }

    /**
     * convert a single question object into text output in the given
     * format.
     * This must be overriden
     * @param object question question object
     * @return mixed question export text or null if not implemented
     */
    function writequestion($question) {
        // if not overidden, then this is an error.
        $formatnotimplemented = get_string('formatnotimplemented','quiz');
        echo "<p>$formatnotimplemented</p>";
        return NULL;
    }

    /**
     * get directory into which export is going
     * @return string file path
     */
    function question_get_export_dir() {
        global $USER;
        if ($this->canaccessbackupdata) {
            $dirname = get_string("exportfilename","quiz");
            $path = $this->course->id.'/backupdata/'.$dirname; // backupdata is protected directory
        } else {
            $path = 'temp/questionexport/' . $USER->id;
        }
        return $path;
    }

    /**
     * Convert the question text to plain text, so it can safely be displayed
     * during import to let the user see roughly what is going on.
     */
    function format_question_text($question) {
        global $DB;
        $formatoptions = new stdClass;
        $formatoptions->noclean = true;
        $formatoptions->para = false;
        if (empty($question->questiontextformat)) {
            $format = FORMAT_MOODLE;
        } else {
            $format = $question->questiontextformat;
        }
        $text = $question->questiontext;
        return format_text(html_to_text($text, 0, false), $format, $formatoptions);
    }

    /**
     * convert files into text output in the given format.
     * @param array
     * @param string encoding method
     * @return string $string
     */
    function writefiles($files, $encoding='base64') {
        if (empty($files)) {
            return '';
        }
        $string = '';
        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }
            $string .= '<file name="' . $file->get_filename() . '" encoding="' . $encoding . '">';
            $string .= base64_encode($file->get_content());
            $string .= '</file>';
        }
        return $string;
    }
}
