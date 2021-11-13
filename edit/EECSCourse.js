//extension of course class, upper level eligibility bool


class EECSCourse extends Course{
  /*
    course = course object
    eligible = bool
  */
  constructor(course_code, prereq, coreq, course_semester, credit_hour, eligible){
    super(course_code, prereq, coreq, course_offer, credit_hour);
    this.eligible = eligible;
  }
}
