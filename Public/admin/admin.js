$("#school").on('change', function () {
    $("#academy").empty();
    $.post(
            url_user_academy_ajax,
            {'school_id': $(this).val()},
    function (result) {
        var academy_options = "<option value=''>请选择</option>";
        for (var i in result) {
            academy_options += "<option value='" + result[i].academy_id + "'>" + result[i].academy_name + "</option>";
        }
        $("#academy").html(academy_options);
    }
    );
});

$("#academy").on('change', function () {
    $("#major").empty();
    $.post(
            url_user_major_ajax,
            {'academy_id': $(this).val()},
    function (result) {
        var major_options = "<option value=''>请选择</option>";
        for (var i in result) {
            major_options += "<option value='" + result[i].major_id + "'>" + result[i].major_name + "</option>";
        }
        $("#major").html(major_options);
    }
    );
});

$("#grade").on('change', function () {
    $("#class").empty();
    $.post(
            url_user_class_ajax,
            {'grade_id': $(this).val(), 'major_id': $('#major').val(), 'academy_id': $("#academy_id").val(),
                'school_id': $("#school").val()},
    function (result) {
        var class_options = "<option value=''>请选择</option>";
        for (var i in result) {
            class_options += "<option value='" + result[i].class_id + "'>" + result[i].class_name + "</option>";
        }
        $("#class").html(class_options);
    }
    );
});

function form_submit(type_num) {

    var user_name = $('#user_name').val();
    var user_gender = $('#user_gender').val();

    var school = $('#school').val();
    var school_name = $('#school option:selected').text();
//  if (school == null || school == "") {
//    alert("请填写完整组织信息");
//    return false;
//  }

    var academy = $('#academy').val();
    var academy_name = $('#academy option:selected').text();
//  if (academy == null || academy == "") {
//    alert("请填写完整组织信息");
//    return false;
//  }
    var major = $('#major').val();
    var major_name = $('#major option:selected').text();
//  if (major == null || major == "") {
//    alert("请填写完整组织信息");
//    return false;
//  }
    var grade = $('#grade').val();
    var grade_name = $('#grade option:selected').text();
//  if (grade == null || grade == "") {
//    alert("请填写完整组织信息");
//    return false;
//  }
    var user_class = $('#class').val();
    var user_class_name = $('#class option:selected').text();
//  if (user_class == null || user_class == "") {
//    alert("请填写完整组织信息");
//    return false;
//  }
    var group = $('#group').val();
    var group_name = $('#group option:selected').text();
//  if (group == null || group == "") {
//    alert("请填写完整组织信息");
//    return false;
//  }

    var type = $('#type').val();
    var uid = $("#uid").val();
    var password = $('#password').val();
    if (type_num == 1) {
        $.post(
                url_user_add,
                {
                    'user_name': user_name, 'user_gender': user_gender, 'school': school, 'academy': academy,
                    'major': major, 'grade': grade, 'class': user_class, 'group': group, 'type': type, 'password': password, 'school_name': school_name, 'academy_name': academy_name,
                    'major_name': major_name, 'grade_name': grade_name, 'class_name': user_class_name, 'group_name': group_name
                },
        function (data) {
            if (data == 0 | data == null) {
                alert('请正确填写用户信息!');
            } else {
                alert('操作成功!');
            }
        }
        );
    } else {
        $.post(
                url_user_update,
                {
                    'uid': uid, 'user_name': user_name, 'user_gender': user_gender, 'school': school, 'academy': academy,
                    'major': major, 'grade': grade, 'class': user_class, 'group': group, 'type': type, 'password': password
                },
        function (data) {
            if (data == 0 | data == null) {
                alert('请正确填写用户信息!');
            } else {
                alert('操作成功!');
            }
        }
        );
    }
}


function checked_submit() {
    var uids = "";
    var i = 1;
    $("input[name='list_check']:checked").each(function () {
        i++;
    });
    if (i == 1) {
        alert("请选择人员");
        return;
    }
    var k = 0;
    $("input[name='list_check']:checked").each(function () {
        if (k < i - 2) {
            uids += $(this).attr('id') + ',';
        } else {
            uids += $(this).attr('id');
        }
        k++;
    });
    doconfirm('确定要通过审核吗？', url_check_submit + "?uids=" + uids);
}

function set_org() {
    var uids = "";
    var i = 1;
    $("input[name='list_check']:checked").each(function () {
        i++;
    });
    if (i == 1) {
        alert("请选择");
        return;
    }
    var k = 0;
    $("input[name='list_check']:checked").each(function () {
        if (k < i - 2) {
            uids += $(this).attr('id') + ',';
        } else {
            uids += $(this).attr('id');
        }
        k++;
    });
    window.location.href = url_set_org + '?uids=' + uids;

}

function set_org_save() {
    var school = $('#school').val();
    var school_name = $('#school option:selected').text();
    if (school == null || school == "") {
        alert("请填写完整组织信息");
        return false;
    }

    var academy = $('#academy').val();
    var academy_name = $('#academy option:selected').text();
    if (academy == null || academy == "") {
        alert("请填写完整组织信息");
        return false;
    }
    var major = $('#major').val();
    var major_name = $('#major option:selected').text();
    if (major == null || major == "") {
        alert("请填写完整组织信息");
        return false;
    }
    var grade = $('#grade').val();
    var grade_name = $('#grade option:selected').text();
    if (grade == null || grade == "") {
        alert("请填写完整组织信息");
        return false;
    }
    var user_class = $('#class').val();
    var user_class_name = $('#class option:selected').text();
    if (user_class == null || user_class == "") {
        alert("请填写完整组织信息");
        return false;
    }
    var group = $('#group').val();
    var group_name = $('#group option:selected').text();
    if (group == null || group == "") {
        alert("请填写完整组织信息");
        return false;
    }
    var uids = $('#uids').val();
    $.post(
            url_set_org,
            {
                'uids': uids, 'school': school, 'academy': academy,
                'major': major, 'grade': grade, 'class': user_class, 'group': group
                , 'school_name': school_name, 'academy_name': academy_name,
                'major_name': major_name, 'grade_name': grade_name, 'class_name': user_class_name, 'group_name': group_name
            },
    function (data) {
        doconfirm(data.info, data.url);
    }
    );
}

function delete_school() {
    var uids = "";
    var i = 1;
    $("input[name='list_check']:checked").each(function () {
        i++;
    });
    if (i == 1) {
        alert("请选择");
        return;
    }
    var k = 0;
    $("input[name='list_check']:checked").each(function () {
        if (k < i - 2) {
            uids += $(this).attr('id') + ',';
        } else {
            uids += $(this).attr('id');
        }
        k++;
    });
    doconfirm('确定删除吗', url_delete_school + '?school_ids=' + uids)
}

function delete_academy() {
    var uids = "";
    var i = 1;
    $("input[name='list_check']:checked").each(function () {
        i++;
    });
    if (i == 1) {
        alert("请选择");
        return;
    }
    var k = 0;
    $("input[name='list_check']:checked").each(function () {
        if (k < i - 2) {
            uids += $(this).attr('id') + ',';
        } else {
            uids += $(this).attr('id');
        }
        k++;
    });
    doconfirm("确定要进行删除吗?", url_delete_academy + '?academy_ids=' + uids);
}

function delete_by_ids(option_url) {
    var uids = "";
    var i = 1;
    $("input[name='list_check']:checked").each(function () {
        i++;
    });
    if (i == 1) {
        alert("请选择");
        return;
    }
    var k = 0;
    $("input[name='list_check']:checked").each(function () {
        if (k < i - 2) {
            uids += $(this).attr('id') + ',';
        } else {
            uids += $(this).attr('id');
        }
        k++;
    });
    doconfirm("确定要进行删除吗?", option_url + '?ids=' + uids);
}

function get_teacher_list(url) {
    var school = $('#school').val();
    var academy = $('#academy').val();
    var major = $('#major').val();
    var grade = $('#grade').val();
    $.post(
            url,
            {
                'school': school, 'academy': academy,
                'major': major, 'grade': grade
            },
    function (data) {
        if (data == 0 | data == null) {
            return;
        } else {
            var i = 0;
            var options = "";
            for (var j in data) {
                options += "<option value='" + data[i].user_id + "'>" + data[i].user_name + "</option>";

                i++;
            }
            $("#teacher").html(options)
        }
    }
    );
}

function get_class_member_list(url) {
    var school = $('#school').val();
    var academy = $('#academy').val();
    var major = $('#major').val();
    var grade = $('#grade').val();
    var user_class = $('#class').val();
    var options = "";
    if (user_class == false) {
        $("#user_name_tages_1").html('请选择班级');
        return;
    }
    $.post(
            url,
            {
                'school': school, 'academy': academy,
                'major': major, 'grade': grade, 'class': user_class
            },
    function (data) {

        if (data == 0 | data == null) {

        } else {
            var i = 0;

            for (var j in data) {
                options += "<span class='btn btn-sm' id='" + data[i].user_id + "' onclick='addtoright(this)'>" + data[i].user_name + "</span>&nbsp;";

                i++;
            }
            $("#user_name_tages_1").html(options)
        }
    }
    );
}

function addtoright(obj) {
    var user_id = $(obj).attr('id');
    var user_name = $(obj).text();
    var tag = "<span class='tag' id='tag_right_" + user_id + "' user_id='" + user_id + "' user_name='" + user_name + "'>" + user_name + "<button type='button' class='close' onclick='remove_tag(\"" + user_id + "\")'>×</button></span>";
    var user_list = $("#tags_right").html();
    if (user_list == "请选择人员" || user_list == "") {
        $("#tags_right").html('');
    }
    if ($("#tag_right_" + user_id).attr('user_id')) {

    } else {
        $("#tags_right").append(tag);
    }
}

function remove_tag(html_id) {
    $("#tag_right_" + html_id).remove();
    var user_list = $("#tags_right").html();
    if (user_list == null || user_list == "") {
        $("#tags_right").html('请选择人员');
    }
}

function append_user_list_to_main() {
    var user_list = $("#tags_right").html();
    if (user_list == null || user_list == "" || user_list == "请选择") {
        $("#tags_right").html('请选择');
    } else {
        var user_appended_list = "";
        $("#tags_right").find('span').each(function () {
            var id = $(this).attr('user_id');
            if ($("#appended_" + id).attr('name')) {
            } else {
                user_appended_list += "<span name='appended_user_id' id='appended_" + $(this).attr('user_id') + "' user_id='" + $(this).attr('user_id') + "'>" + $(this).attr('user_name') + "<button type='button' onclick='remove_appended_tag(\"" + $(this).attr('user_id') + "\")'>×</button><input type='text' name='uids[]' class='hidden' value='" + $(this).attr('user_id') + "'></span>";
            }
        });
        $("#appended_list").append(user_appended_list);
        $("#modal-wizard").modal('hide');
    }
}

function remove_appended_tag(uid) {
    $("#appended_" + uid).remove();
}

$('.date-picker').datepicker({format: 'yyyy-mm-dd', autoclose: true}).next().on(ace.click_event, function () {
    $(this).prev().focus();
});


$("#team").on('change', function () {
    var team_id = $("#team option:selected").val();
    var ajax_url = $("#team").attr('url');
    $.post(
            ajax_url,
            {'team_id': team_id},
    function (result) {
        if (result.status = 1) {
            var academy_options = "<option value=''>请选择</option>";
            var datas = result.data;
            for (var i in datas) {
                academy_options += "<option value='" + datas[i].user_id + "'>" + datas[i].user_name + "</option>";
            }
            $("#attack_members").html(academy_options);
            $("#affense_members").html(academy_options);
        } else {
            console.log(result);
        }

    }
    );
});

/**
 * @function common doconfirm function
 * @author Tonysprite
 * @param {type} str
 * @param {type} url
 * @returns {undefined}
 */
function doconfirm(str, url) {
    if (window.confirm(str)) {
        window.location.href = url;
        return;
    }
}



$("#class").on("change", function () {
    var class_name = $("#class option:selected").text();
    $("#class_name").val(class_name)
});
