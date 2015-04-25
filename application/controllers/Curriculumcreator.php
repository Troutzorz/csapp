<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Curriculumcreator extends CI_Controller {
	
	public function index()
	{
		//load models
		$this->load->model('Curriculum_model', 'Curriculum_course_slot_model', 'Course_model');
		$curriculum = new Curriculum_Model(); 
		$_SESSION['maxCurriculumIndex'] = 1;
		$_SESSION['reqs'] = array();
		
		//call and pass data to initial curriculum view
		$curriculums = $curriculum->getAllCurriculums();
		$data = array();
		
		//creating easy to use array for table
		foreach ($curriculums as $curr) 
		{
			$arr = [ 
				'name' => $curr->getName(),
				'id'   => $curr->getCurriculumID(),
				'date' => $curr->getDateCreated(),
			];
			
			array_push($data, $arr);
		}
		$this->load->view('curriculum_choice', array('data'=>$data));
	}
        
    //clone and edit a curriculum
    public function cloneCurriculum($curriculumID = NULL) //post: curriculum
    {
		//get arguments
		if ($curriculumID == NULL)
			$curriculumID = $this->input->post('curriculum');
		$curriculum = new Curriculum_Model();
		$curriculum->loadPropertiesFromPrimaryKey($curriculumID);
		
		$_SESSION['curriculumCreationMethod'] = "clone";
		$_SESSION['curriculum'] = $curriculum->toSerializedString();
		
		//load curriculum
		$this->loadCurriculumEdit($curriculum);
	}
	
	//edit a current curriculum
	public function editCurriculum($curriculumID = NULL) //post: curriculum
	{
		//get arguments
		if ($curriculumID == NULL)
			$curriculumID = $this->input->post('curriculum');
		$curriculum = new Curriculum_Model();
		$curriculum->loadPropertiesFromPrimaryKey($curriculumID);
		
		$_SESSION['curriculumCreationMethod'] = "edit";
		$_SESSION['curriculum'] = $curriculum->toSerializedString();
			
		//load curriculum
		$this->loadCurriculumEdit($curriculum);
	}
	
	//creating a new curriculum
	public function newCurriculum()
	{
		$curriculum = new Curriculum_model(); 
		$curriculum->setName('New Curriculum');
		$_SESSION['curriculumCreationMethod'] = "new";
		$_SESSION['curriculum'] = $curriculum->toSerializedString();
		$data = array(
			'name'    => "New Curriculum",
			'courses' => array(),
			'type'    => ""
		);
		$this->load->view('curriculum_edit', array('data'=>$data));	
	}
	
	//deletes a selected curriculum
	public function deleteCurriculum($curriculumID = NULL) //post: curriculum
	{
		//get arguments
		if ($curriculumID == NULL)
			$curriculumID = $this->input->post('curriculum');
		
		$curriculum = new Curriculum_model();
		$curriculum->loadPropertiesFromPrimaryKey($curriculumID);
		$curriculum->delete();
		
		$this->index();
	}
	
	//saves a curriculum to the database
	public function setCurriculum($name = NULL, $type = NULL) //post: name, type; type being whether the curriculum is a degree, minor, or concentration
	{
		//get arguments
		if ($name == NULL)
			$name = $this->input->post('name');
			
		if ($type == NULL)
			$type = $this->input->post('type');
		
		//set curriculum name
		$curriculum = new Curriculum_model();
		$curriculum->fromSerializedString($_SESSION['curriculum']);
		$curriculum->setName($name);
		
		//set curriculum type
		if ($type == "Degree")
			$curriculum->setCurriculumType(1);
		else if ($type == "Minor")
			$curriculum->setCurriculumType(2);
		else if ($type == "Concentration")
			$curriculum->setCurriculumType(3);

////////////////////////////////////////////////////////////////////////		
			
		$courseSlots = $curriculum->getCurriculumCourseSlots();

		//find and delete old reqs and save new ones
		if (isset($_SESSION['reqs']))
		{
			foreach ($_SESSION['reqs'] as $reqs)
			{
				foreach ($courseSlots as $slot) 
				{
					//find the right course slot
					if ($slot->getCurriculumIndex() == $reqs['slot']->getCurriculumIndex()) 
					{			
						$previousPrereqSlots = $slot->getPrequisiteCourseSlots();
						$previousCoreqSlots  = $slot->getCorequisiteCourseSlots();	
							
						//find old reqs and delete any that should no longer exist
						if (isset($previousPrereqSlots) and isset($reqs['prereqs']))
							foreach ($previousPrereqSlots as $previousSlot)
								$courseSlot->removeCourseSlotRequisite($previousSlot);
								
						if (isset($previousCoreqSlots) and isset($reqs['coreqs']))
							foreach ($previousCoreqSlots as $previousSlot)
								$courseSlot->removeCourseSlotRequisite($previousSlot);
						
						break;
					}
				}
				
				//save new prereqs
				if (isset($reqs['prereqs']))
					foreach ($reqs['prereqs'] as $r)
						$courseSlot->addCourseSlotPrerequisite($r);
				
				if (isset($reqs['coreqs']))
					foreach ($reqs['coreqs'] as $r)
						$courseSlot->addCourseSlotPrerequisite($r);	
			}
		} 
	
////////////////////////////////////////////////////////////////////////		
		
		//save curriculum
		if ($_SESSION['curriculumCreationMethod'] == "edit")
			$curriculum->update(); //update current curriculum for edit
		else
			$curriculum->create(); //create a new entry for clone/new	
					
		unset($_SESSION['curriculum']);
		unset($_SESSION['courseSlot']);
		unset($_SESSION['curriculumCreationMethod']);
		unset($_SESSION['curriculumCourseSlotMethod']);
		unset($_SESSION['maxCurriculumIndex']);
		unset($_SESSION['reqs']);
				
		$this->index();
	}
	
	//cancelling an edit to a curriculum
	public function cancelCurriculum()
	{
		unset($_SESSION['curriculum']);
		unset($_SESSION['courseSlot']);
		unset($_SESSION['curriculumCreationMethod']);
		unset($_SESSION['curriculumCourseSlotMethod']);
		unset($_SESSION['maxCurriculumIndex']);
		unset($_SESSION['reqs']);
		
		$this->index();
	}
		
	//clone and edit a curriculum course slot
    public function cloneCurriculumCourseSlot() 
    {
		$_SESSION['curriculumCourseSlotMethod'] = "clone";
		$this->loadCurriculumCourseSlotEdit();
	}
	
	//clone and edit a curriculum course slot
    public function editCurriculumCourseSlot() 
    {
		$_SESSION['curriculumCourseSlotMethod'] = "edit";
		$this->loadCurriculumCourseSlotEdit();
	}

	//create a new curriculum course slot
	public function newCurriculumCourseSlot()
	{
		$_SESSION['curriculumCourseSlotMethod'] = "new";
		$this->loadCurriculumCourseSlotEdit();
	}
	
	//delete a curriculum course slot
	public function deleteCurriculumCourseSlot($courseSlotIndex = NULL) 
	{
		//get arguments
		if ($courseSlotIndex == NULL)
			$courseSlotIndex = $this->input->post('courseSlot');
	
		$curriculum = new Curriculum_model();
		$curriculum->fromSerializedString($_SESSION['curriculum']);
		$courseSlots = $curriculum->getCurriculumCourseSlots();
		$courseSlot = new Curriculum_course_slot_model();
		
		//match indeces
		foreach ($courseSlots as $slot)
		{
			$index = $slot->getCurriculumIndex();
			if ($index == $courseSlotIndex)
			{	
				$courseSlot = $slot;
				break;
			}
		}
		
		$curriculum->removeCurriculumCourseSlot($courseSlot);
		$_SESSION['curriculum'] = $curriculum->toSerializedString();
		
		$courseSlot->delete();
		
		//load curriculum
		$this->loadCurriculumEdit($curriculum);
	}
	
	//cancel a curriculum course slot editing
	public function cancelCurriculumCourseSlot()
	{
		$curriculum = new Curriculum_model();
		$curriculum->fromSerializedString($_SESSION['curriculum']);
		
		$this->loadCurriculumEdit($curriculum); 
	}
	
	//save a curriculum course slot
	//validCourseIDs(int array); name(string); minimumGrade(string); 
	public function setCurriculumCourseSlot($validCourseIDs = NULL, $name = NULL, $minimumGrade = NULL, $recommendedQuarter = NULL, $recommendedYear = NULL, $notes = NULL, $index = NULL, $prereqIDs = NULL, $coreqIDs = NULL) 
	{
		//get arguments
		if ($validCourseIDs == NULL)
			$validCourseIDs = $this->input->post('validCourseIDs');
			
		if ($name == NULL)
			$name = $this->input->post('name');
			
		if ($minimumGrade == NULL)
			$minimumGrade = $this->input->post('minimumGrade');
			
		if ($recommendedQuarter == NULL)
			$recommendedQuarter = $this->input->post('recommendedQuarter');
			
		if ($recommendedYear == NULL)
			$recommendedYear = $this->input->post('recommendedYear');
		
		if ($notes == NULL)
			$notes = $this->input->post('notes');
			
		if ($index == NULL)
			$index = $this->input->post('index');
			
		if ($prereqIDs == NULL)
			$prereqIDs = $this->input->post('prereqIDs');
			
		if ($prereqIDs == NULL)
			$prereqIDs = $this->input->post('coreqIDs');
			
		if (!isset($notes))
			$notes = " ";
				
		//add logic to grab arguments	
		$courseSlot = new Curriculum_course_slot_model();
		$courseSlot->fromSerializedString($_SESSION['courseSlot']);
		$courseSlot->setMinimumGrade($minimumGrade);
		$courseSlot->setName($name);
		$courseSlot->setRecommendedQuarter($recommendedQuarter);
		$courseSlot->setRecommendedYear($recommendedYear);
		$courseSlot->setNotes($notes);
		
		$curriculum = new Curriculum_model();
		$curriculum->fromSerializedString($_SESSION['curriculum']);
		
////////////////////////////////////////////////////////////////////////
		
		$courseSlots = $curriculum->getCurriculumCourseSlots();
		$prerequisites = array();
		$corequisites  = array();
		
		$largestIndex = 0;
		//Handle non-unique indeces
		foreach ($courseSlots as $slot)
		{
			$currentIndex = $slot->getCurriculumIndex();
			if ($currentIndex > $largestIndex)
				$largestIndex = $currentIndex;
				
			//grab prereq course slots 
			if (isset($prereqIDs))
				foreach ($prereqIDs as $p)
					if ($currentIndex == $p)
						array_push($prerequisites, $slot);
			
			if (isset($coreqIDs))
				foreach ($coreqIDs as $p)
					if ($currentIndex == $p)
						array_push($corequisites, $slot);
		}
				
		if ($largestIndex > 0)
			$_SESSION['maxCurriculumIndex'] = $largestIndex + 1;
		
		if (isset($prerequisites) or isset($corequisites))
		{
			//be sure to replace changes
			if (isset($_SESSION['reqs']))
			{
				$currIndex = $courseSlot->getCurriculumIndex();
				foreach ($_SESSION['reqs'] as $reqs)
					if ($reqs['slot']->getCurriculumIndex() == $currIndex)
						unset($reqs);

			}
					
			
			$arr = [
				'slot'    => $courseSlot,
				'prereqs' => array(),
				'coreqs'  => array() 
			];
			
			if (isset($prerequisites))
				foreach ($prerequisites as $p)
					array_push($arr['prereqs'], $p);
			
			//add co
			
			array_push($_SESSION['reqs'], $arr);
		}
		
///////////////////////////////////////////////////////////////////////					
		
		//remove previous valid courses
		$previousValidCourseIDs = $courseSlot->getValidCourseIDs();
		if (isset($previousValidCourseIDs))
			foreach ($previousValidCourseIDs as $prevID)
				$courseSlot->removeValidCourseID($prevID);

		//populate course slot with the new valid course ids
		foreach ($validCourseIDs as $validCourse)
			$courseSlot->addValidCourseID($validCourse);
					
		if (strcmp($_SESSION['curriculumCourseSlotMethod'], 'edit') == 0)
		{
			$tempCourseSlot = new Curriculum_course_slot_model();
			$tempCourseSlot->fromSerializedString($_SESSION['courseSlot']);
			$tempCourseSlotIndex = $tempCourseSlot->getCurriculumIndex();			
			
			foreach ($courseSlots as $slot)
			{
				if ($tempCourseSlotIndex == $slot->getCurriculumIndex())
				{
					$curriculum->removeCurriculumCourseSlot($tempCourseSlot);
					break;
				}
			}
		} 
		else 
			$courseSlot->setCurriculumIndex($_SESSION['maxCurriculumIndex']++);

		$curriculum->addCurriculumCourseSlot($courseSlot);
		
		$_SESSION['courseSlot'] = $courseSlot->toSerializedString();
		$_SESSION['curriculum'] = $curriculum->toSerializedString();
		
		$this->loadCurriculumEdit($curriculum);   
	}

	//passes data to and loads curriculum course slot edit view
	private function loadCurriculumCourseSlotEdit($courseSlotIndex = NULL)
	{
		///get arguments
		if ($courseSlotIndex == NULL)
			$courseSlotIndex = $this->input->post('courseSlot'); 
		
		$curriculum = new Curriculum_model();
		$curriculum->fromSerializedString($_SESSION['curriculum']);
		$courseSlots = $curriculum->getCurriculumCourseSlots();
		$courseSlot = new Curriculum_course_slot_model();
		
		//match indeces
		foreach ($courseSlots as $slot)
		{
			$index = $slot->getCurriculumIndex();
			if ($index == $courseSlotIndex)
			{	
				$courseSlot = $slot;
				break;
			}
		}
		
		if ($courseSlot->getName() == NULL)
			$courseSlot->setName("New Curriculum Course Slot");
		
		$courses = new Course_model();
		
		$data = array(
			'name'               => $courseSlot->getName(),
			'courses'            => array(),
			'recommendedQuarter' => $courseSlot->getRecommendedQuarter(),
			'recommendedYear'    => $courseSlot->getRecommendedYear(),
			'minimumGrade'       => $courseSlot->getMinimumGrade(),
			'notes'              => $courseSlot->getNotes(),
			'index'				 => $courseSlotIndex,
			'prereqs'            => array(),
			'coreqs'             => array(), 
		);
		
		//get all available courses and pass to data
		$availableCourses = $courses->getAllCourses();
		$validCourse = $courseSlot->getValidCourseIDs();
		
		foreach ($availableCourses as $course)
		{
			$arr = [
				'name'    => $course->getCourseName(),
				'id'      => $course->getCourseID(),
				'prereqs' => $course->getPrerequisiteCourses(),
				'number'  => $course->getCourseNumber(),
				'selected'=> FALSE
			];
			
			foreach ($validCourse as $valid)
				if (strcmp($valid, $course->getCourseID()) == 0)
					$arr['selected'] = TRUE;
			
			array_push($data['courses'], $arr);
		}
		
////////////////////////////////////////////////////////////////////////		
		
		//find if the co or pre reqs have been edited in this session
		$prereqsEdited = FALSE;
		$coreqsEdited  = FALSE;
		
		foreach ($_SESSION['reqs'] as $reqs)
		{
			foreach ($courseSlots as $slot) 
			{
				//find the right course slot
				if ($slot->getCurriculumIndex() == $reqs['slot']->getCurriculumIndex()) 
				{			
					if (isset($reqs['prereqs']))
						$prereqsEdited = TRUE;
					if (isset($reqs['coreqs']))
						$coreqsEdited = TRUE;
				}
			}
		}
		
		//Pass possible and chosen prereq slots
		$currentIndex = $courseSlot->getCurriculumIndex();
		
		foreach ($courseSlots as $slot)
		{
			$arr = [ 
				'name'     => $slot->getName(),
				'id'       => $slot->getCurriculumCourseSlotID(),
				'index'    => $slot->getCurriculumIndex(),
				'selected' => FALSE
			];
			
			if (!$prereqsEdited)
			{	//normal prereq functionality
				$slotPrereqs = $slot->getPrequisiteCourseSlots();
				if (isset($slotPrereqs))
					foreach ($slotPrereqs as $prereq)
						if ($prereq->getCurriculumIndex() == $arr['index'])
							$arr['selected'] = TRUE;
			}
			else
			{	//grabbing prereqs from session
				foreach ($_SESSION['reqs'] as $reqs)
					if ($reqs->getCurriculumIndex() == $arr['index'])
						$arr['selected'] = TRUE;
			}
			
			//add co
			
			if ($currentIndex != $arr['index'])
					array_push($data['prereqs'], $arr);
		}
				
////////////////////////////////////////////////////////////////////////		


		$_SESSION['courseSlot'] = $courseSlot->toSerializedString();
		$this->load->view('course_slot_edit', array('data'=>$data));
	}
	
	//passes data to and loads curriculum edit view
	private function loadCurriculumEdit($curriculum)
	{
		$courseSlots = $curriculum->getCurriculumCourseSlots();
		
		$type = $curriculum->getCurriculumType();
		$curriculumType = NULL;
		if ($type == 1)
			$curriculumType = 'Degree';
		else if ($type == 2)
			$curriculumType = 'Minor';
		else if ($type == 3) 
			$curriculumType = 'Concentration';
		
		$data = array(
			'name'   => $curriculum->getName(),
			'course' => array(),
			'type'   => $curriculumType
		);
		
		//create easy to use array for table
		foreach ($courseSlots as $slot)
		{
			$arr = [ 
				'name' => $slot->getName(),
				'id'   => $slot->getCurriculumCourseSlotID(),
				'index'=> $slot->getCurriculumIndex()
			];
			
			array_push($data['course'], $arr);
		}
		
		$this->load->view('curriculum_edit', array('data'=>$data)); 
	}
}
